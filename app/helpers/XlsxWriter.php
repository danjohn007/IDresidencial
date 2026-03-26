<?php
/**
 * XlsxWriter – Generador simple de archivos .xlsx (multi-hoja)
 *
 * Crea un archivo XLSX válido usando ZipArchive y XML nativo de PHP.
 * No requiere librerías externas.
 *
 * Uso:
 *   $writer = new XlsxWriter();
 *   $writer->addSheet('Hoja1', ['Col A', 'Col B'], [['val1','val2']]);
 *   $writer->download('archivo.xlsx');
 */
class XlsxWriter {

    /** @var array  Lista de hojas: ['name'=>string, 'headers'=>[], 'rows'=>[]] */
    private $sheets = [];

    /**
     * Agrega una hoja al libro.
     *
     * @param string $name    Nombre de la hoja (tab)
     * @param array  $headers Fila de encabezados
     * @param array  $rows    Filas de datos (array de arrays)
     */
    public function addSheet($name, array $headers, array $rows = []) {
        $this->sheets[] = [
            'name'    => $name,
            'headers' => $headers,
            'rows'    => $rows,
        ];
    }

    /**
     * Envía el archivo XLSX al navegador como descarga.
     *
     * @param string $filename Nombre del archivo a descargar
     */
    public function download($filename) {
        $content = $this->build();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $content;
        exit;
    }

    /**
     * Construye y devuelve el contenido binario del XLSX.
     *
     * @return string Contenido binario del ZIP/XLSX
     */
    public function build() {
        // Recopilar todas las cadenas compartidas
        $sharedStrings = [];
        $sharedIndex   = [];

        $getStringIndex = function($str) use (&$sharedStrings, &$sharedIndex) {
            $key = (string)$str;
            if (!isset($sharedIndex[$key])) {
                $sharedIndex[$key]  = count($sharedStrings);
                $sharedStrings[]    = $key;
            }
            return $sharedIndex[$key];
        };

        // Pre-indexar encabezados y datos de todas las hojas
        foreach ($this->sheets as &$sheet) {
            foreach ($sheet['headers'] as $h) {
                $getStringIndex($h);
            }
            foreach ($sheet['rows'] as $row) {
                foreach ($row as $cell) {
                    if (!is_numeric($cell)) {
                        $getStringIndex((string)$cell);
                    }
                }
            }
        }
        unset($sheet);

        // Crear ZIP en memoria
        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::OVERWRITE);

        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', $this->buildContentTypes());

        // _rels/.rels
        $zip->addFromString('_rels/.rels', $this->buildRootRels());

        // xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbook());

        // xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRels());

        // xl/styles.xml
        $zip->addFromString('xl/styles.xml', $this->buildStyles());

        // xl/sharedStrings.xml
        $zip->addFromString('xl/sharedStrings.xml', $this->buildSharedStrings($sharedStrings));

        // xl/worksheets/sheetN.xml
        foreach ($this->sheets as $i => $sheet) {
            $sheetNum = $i + 1;
            $zip->addFromString(
                "xl/worksheets/sheet{$sheetNum}.xml",
                $this->buildWorksheet($sheet, $getStringIndex)
            );
        }

        $zip->close();

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);

        return $content;
    }

    // ─── XML builders ────────────────────────────────────────────────────────

    private function buildContentTypes() {
        $sheets = '';
        foreach ($this->sheets as $i => $_) {
            $n = $i + 1;
            $sheets .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml"'
                . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n";
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n"
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n"
            . '<Default Extension="xml" ContentType="application/xml"/>' . "\n"
            . '<Override PartName="/xl/workbook.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n"
            . '<Override PartName="/xl/styles.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . "\n"
            . '<Override PartName="/xl/sharedStrings.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>' . "\n"
            . $sheets
            . '</Types>';
    }

    private function buildRootRels() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
            . ' Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function buildWorkbook() {
        $sheets = '';
        foreach ($this->sheets as $i => $sheet) {
            $n    = $i + 1;
            $name = htmlspecialchars($sheet['name'], ENT_XML1, 'UTF-8');
            $sheets .= '<sheet name="' . $name . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>' . "\n";
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheets . '</sheets>'
            . '</workbook>';
    }

    private function buildWorkbookRels() {
        $rels = '';
        foreach ($this->sheets as $i => $_) {
            $n = $i + 1;
            $rels .= '<Relationship Id="rId' . $n . '"'
                . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
                . ' Target="worksheets/sheet' . $n . '.xml"/>' . "\n";
        }
        // sharedStrings relationship
        $ssId = count($this->sheets) + 1;
        $rels .= '<Relationship Id="rId' . $ssId . '"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"'
            . ' Target="sharedStrings.xml"/>' . "\n";
        $styId = $ssId + 1;
        $rels .= '<Relationship Id="rId' . $styId . '"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"'
            . ' Target="styles.xml"/>' . "\n";

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    private function buildStyles() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="2">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    private function buildSharedStrings(array $strings) {
        $count = count($strings);
        $items = '';
        foreach ($strings as $s) {
            $items .= '<si><t>' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' count="' . $count . '" uniqueCount="' . $count . '">'
            . $items
            . '</sst>';
    }

    private function buildWorksheet(array $sheet, callable $getStringIndex) {
        $rows = '';

        // Fila de encabezados (estilo negrita: s="1")
        $headerCells = '';
        foreach ($sheet['headers'] as $col => $header) {
            $cellRef     = $this->cellRef($col, 1);
            $strIdx      = $getStringIndex($header);
            $headerCells .= '<c r="' . $cellRef . '" t="s" s="1"><v>' . $strIdx . '</v></c>';
        }
        $rows .= '<row r="1">' . $headerCells . '</row>';

        // Filas de datos
        foreach ($sheet['rows'] as $rowNum => $rowData) {
            $rowIdx   = $rowNum + 2; // 1=header, datos desde 2
            $rowCells = '';
            foreach ($rowData as $col => $value) {
                $cellRef = $this->cellRef($col, $rowIdx);
                if ($value !== '' && is_numeric($value)) {
                    $rowCells .= '<c r="' . $cellRef . '"><v>' . htmlspecialchars((string)$value, ENT_XML1, 'UTF-8') . '</v></c>';
                } else {
                    $strIdx    = $getStringIndex((string)$value);
                    $rowCells .= '<c r="' . $cellRef . '" t="s"><v>' . $strIdx . '</v></c>';
                }
            }
            $rows .= '<row r="' . $rowIdx . '">' . $rowCells . '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . $rows . '</sheetData>'
            . '</worksheet>';
    }

    /**
     * Convierte (columna 0-based, fila 1-based) a referencia de celda Excel (ej. A1, B2).
     */
    private function cellRef($col, $row) {
        $letters = '';
        $c = $col;
        do {
            $letters = chr(65 + ($c % 26)) . $letters;
            $c       = intdiv($c, 26) - 1;
        } while ($c >= 0);
        return $letters . $row;
    }
}
