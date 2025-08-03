/**
 * Safe database query helper
 */
protected function _safe_table_exists($table_name)
{
    try {
        return $this->db->table_exists($table_name);
    } catch (Exception $e) {
        log_message('error', 'Table check error: ' . $e->getMessage());
        return false;
    }
}