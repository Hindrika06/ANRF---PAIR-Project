<?php
if(session_status()===PHP_SESSION_NONE)session_start(); $_SESSION = array (
  'username' => 'superadmin',
  'user_id' => 10,
  'role' => 'super_admin',
  'institute_prefix' => 'uoh',
  'active_prefix' => 'cuk',
); $_GET = array (
  'id' => 4,
  'prefix' => 'cuk',
); include 'admin/export_progress_report_pdf.php';