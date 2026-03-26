<?php
/**
 * XlsxReader – Lector simple de archivos .xlsx (multi-hoja)
 *
 * Lee un archivo XLSX usando ZipArchive y SimpleXML nativo de PHP.
 * No requiere librerías externas.
 *
 * Uso:
 *   $reader = new XlsxReader('/ruta/al/archivo.xlsx');
 *   $sheets = $reader->getSheets();
 *   // $sheets = ['NombreHoja' => [['col1','col2',...], ['val1','val2',...]], ...]
 */
class XlsxReader {

    /** @var string Ruta al archivo .xlsx */
    private $filePath;

    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    /**
     * Lee todas las hojas del archivo y devuelve sus datos.
     *
     * @return array  Asociativo: ['NombreHoja' => [fila0, fila1, ...], ...]
     *                Cada fila es un array de valores (strings/números).
     * @throws RuntimeException Si el archivo no puede abrirse o es inválido.
     */
    public function getSheets() {
        $zip = new ZipArchive();
        if ($zip->open($this->filePath) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo XLSX');
        }

        // Leer cadenas compartidas
        $sharedStrings = $this->readSharedStrings($zip);

        // Leer lista de hojas desde workbook.xml
        $sheetList = $this->readSheetList($zip);

        // Leer cada hoja
        $result = [];
        foreach ($sheetList as $sheetInfo) {
            $name = $sheetInfo['name'];
            $path = $sheetInfo['path'];

            $xml = $this->readZipEntry($zip, $path);
            if ($xml === null) continue;

            $rows = $this->parseWorksheet($xml, $sharedStrings);
            if (!empty($rows)) {
                $result[$name] = $rows;
            }
        }

        $zip->close();

        return $result;
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /**
     * Lee xl/sharedStrings.xml y devuelve el array de cadenas.
     */
    private function readSharedStrings(ZipArchive $zip) {
        $strings = [];
        $xml     = $this->readZipEntry($zip, 'xl/sharedStrings.xml');
        if ($xml === null) return $strings;

        $sst = $this->parseXml($xml);
        if ($sst === false) return $strings;

        $sst->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $sis = $sst->xpath('//x:si');
        if (!$sis) return $strings;

        foreach ($sis as $si) {
            // Concatenar todos los nodos <t> dentro de <si>
            $text = '';
            $si->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $tNodes = $si->xpath('.//x:t');
            if ($tNodes) {
                foreach ($tNodes as $t) {
                    $text .= (string)$t;
                }
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * Lee xl/workbook.xml y xl/_rels/workbook.xml.rels para obtener
     * la lista de hojas con su nombre y ruta dentro del ZIP.
     *
     * @return array  [['name'=>string, 'path'=>string], ...]
     */
    private function readSheetList(ZipArchive $zip) {
        $sheets = [];

        // Leer relaciones del workbook para mapear rId → target
        $relsXml = $this->readZipEntry($zip, 'xl/_rels/workbook.xml.rels');
        $rIdToPath = [];
        if ($relsXml !== null) {
            $rels = $this->parseXml($relsXml);
            if ($rels !== false) {
                $rels->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
                $relationships = $rels->xpath('//r:Relationship');
                if ($relationships) {
                    foreach ($relationships as $rel) {
                        $type   = (string)$rel['Type'];
                        $target = (string)$rel['Target'];
                        $id     = (string)$rel['Id'];
                        if (strpos($type, 'worksheet') !== false) {
                            // Target puede ser relativo como "worksheets/sheet1.xml"
                            if (strpos($target, '/') !== 0) {
                                $target = 'xl/' . $target;
                            }
                            $rIdToPath[$id] = $target;
                        }
                    }
                }
            }
        }

        // Leer workbook.xml para obtener nombres de hojas y sus rId
        $wbXml = $this->readZipEntry($zip, 'xl/workbook.xml');
        if ($wbXml === null) return $sheets;

        $wb = $this->parseXml($wbXml);
        if ($wb === false) return $sheets;

        $wb->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $wb->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $sheetNodes = $wb->xpath('//x:sheet');
        if (!$sheetNodes) return $sheets;

        foreach ($sheetNodes as $sheet) {
            $name = (string)$sheet['name'];
            $rId  = (string)$sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            $path = isset($rIdToPath[$rId]) ? $rIdToPath[$rId] : null;
            if ($path !== null) {
                $sheets[] = ['name' => $name, 'path' => $path];
            }
        }

        return $sheets;
    }

    /**
     * Parsea un worksheet XML y devuelve un array de filas.
     * Cada fila es un array de valores en orden de columna (A, B, C...).
     */
    private function parseWorksheet($xml, array $sharedStrings) {
        $ws = $this->parseXml($xml);
        if ($ws === false) return [];

        $ws->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rowNodes = $ws->xpath('//x:row');
        if (!$rowNodes) return [];

        $rows = [];
        foreach ($rowNodes as $rowNode) {
            $rowIdx = (int)$rowNode['r'] - 1; // 0-based
            $cells  = [];

            $rowNode->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $cellNodes = $rowNode->xpath('x:c');
            if (!$cellNodes) continue;

            foreach ($cellNodes as $cell) {
                $ref  = (string)$cell['r'];
                $type = (string)$cell['t'];

                // Use direct property access instead of XPath to avoid namespace issues
                $rawValue = isset($cell->v) ? (string)$cell->v : '';

                // Resolver tipo
                if ($type === 's') {
                    // Shared string
                    $idx   = (int)$rawValue;
                    $value = isset($sharedStrings[$idx]) ? $sharedStrings[$idx] : '';
                } elseif ($type === 'inlineStr') {
                    $value = isset($cell->is->t) ? (string)$cell->is->t : '';
                } else {
                    $value = $rawValue;
                }

                $colIdx        = $this->colIndex($ref);
                $cells[$colIdx] = $value;
            }

            if (!empty($cells)) {
                // Rellenar huecos y construir fila como array indexado
                $maxCol = max(array_keys($cells));
                $row    = [];
                for ($c = 0; $c <= $maxCol; $c++) {
                    $row[] = isset($cells[$c]) ? $cells[$c] : '';
                }
                $rows[$rowIdx] = $row;
            }
        }

        // Ordenar por índice de fila y re-indexar
        ksort($rows);
        return array_values($rows);
    }

    /**
     * Convierte la referencia de celda Excel (p.e. "B3") al índice de columna 0-based.
     */
    private function colIndex($cellRef) {
        // Extraer solo las letras (p.e. "AB" de "AB12")
        preg_match('/^([A-Z]+)/', strtoupper($cellRef), $m);
        if (empty($m[1])) return 0;
        $letters = $m[1];
        $index   = 0;
        $len     = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    /**
     * Lee una entrada del ZIP y la devuelve como string, o null si no existe.
     */
    private function readZipEntry(ZipArchive $zip, $name) {
        $content = $zip->getFromName($name);
        return ($content !== false) ? $content : null;
    }

    /**
     * Parsea un string XML suprimiendo errores y devuelve SimpleXMLElement o false.
     */
    private function parseXml($xml) {
        libxml_use_internal_errors(true);
        $obj = simplexml_load_string($xml);
        libxml_clear_errors();
        return $obj;
    }
}
