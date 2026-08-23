<?php
if (!defined('ABSPATH')) exit;

class BFMSF_Export {
    public static function export_pdf($form_id) {
        // Check if dompdf is available
        if (!class_exists('Dompdf\Dompdf')) {
            wp_die('PDF export requires dompdf library. Please install it.');
        }
        $submissions = BFMSF_Settings::get_submissions($form_id);
        $fields = BFMSF_Settings::get_fields($form_id);
        // Build HTML table
        $html = '<h1>Form Entries</h1><table border="1"><tr>';
        foreach ($fields as $f) $html .= '<th>' . $f->field_label . '</th>';
        $html .= '</tr>';
        foreach ($submissions as $sub) {
            $data = json_decode($sub->submission_data, true);
            $html .= '<tr>';
            foreach ($fields as $f) {
                $val = isset($data[$f->field_name]) ? $data[$f->field_name] : '';
                if (is_array($val)) $val = implode(', ', $val);
                $html .= '<td>' . $val . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('submissions.pdf');
        exit;
    }

    public static function export_excel($form_id) {
        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            wp_die('Excel export requires PhpOffice/PhpSpreadsheet library.');
        }
        // Similar to PDF but using Spreadsheet
        // ...
    }
}