<?php
defined("isInSideApplication")?null:die('no access');

require_once('config/config.access.php');
require_once('functions/function.debug.php');
require_once('functions/function.build_error_message.php');
require_once('functions/function.log_error.php');
require_once('functions/function.error_handler.php');
require_once('functions/function.script_run_time.php');
require_once('config/config.defaults.php');
require_once('config/config.load_db.php');
require_once('functions/function.new_smarty.php');
require_once('functions/function.login_check.php');
require_once('functions/function.data_submit.php');
require_once('functions/function.build_query.php');
require_once('functions/function.cast_object.php');
require_once('functions/function.comment.php');
require_once('lang/en_gb.php');
