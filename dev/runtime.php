<?php
set_include_path('.:' . dirname(__FILE__));

function __autoload($class) {
  
    // SUPERLOAD-BEGIN
	static $map = array (
	  'IllegalArgumentException' => 'vendor/base-php/inc/base.php',
	  'IllegalStateException' => 'vendor/base-php/inc/base.php',
	  'IOException' => 'vendor/base-php/inc/base.php',
	  'NotFoundException' => 'vendor/base-php/inc/base.php',
	  'SecurityException' => 'vendor/base-php/inc/base.php',
	  'SyntaxException' => 'vendor/base-php/inc/base.php',
	  'UnsupportedOperationException' => 'vendor/base-php/inc/base.php',
	  'Base' => 'vendor/base-php/inc/base.php',
	  'Callback' => 'vendor/base-php/inc/base.php',
	  'FunctionCallback' => 'vendor/base-php/inc/base.php',
	  'InstanceCallback' => 'vendor/base-php/inc/base.php',
	  'StaticCallback' => 'vendor/base-php/inc/base.php',
	  'Inflector' => 'vendor/base-php/inc/base.php',
	  'Date' => 'vendor/base-php/inc/date.php',
	  'Date_Time' => 'vendor/base-php/inc/date.php',
	  'File' => 'vendor/base-php/inc/file.php',
	  'UploadedFile' => 'vendor/base-php/inc/file.php',
	  'UploadedFileError' => 'vendor/base-php/inc/file.php',
	  'MIME' => 'vendor/base-php/inc/mime.php',
	  'MoneyConversionException' => 'vendor/base-php/inc/money.php',
	  'Money' => 'vendor/base-php/inc/money.php',
	  'MoneyBank' => 'vendor/base-php/inc/money.php',
	  'ISO_Country' => 'vendor/base-php/inc/iso/country.php',
	  'ISO_Language' => 'vendor/base-php/inc/iso/language.php',
	  'GDBException' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBQueryException' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBIntegrityConstraintViolation' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBForeignKeyViolation' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBUniqueViolation' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBCheckViolation' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDB' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBMySQL' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBResult' => 'vendor/base-php/inc/gdb/gdb.php',
	  'GDBResultMySQL' => 'vendor/base-php/inc/gdb/gdb.php',
	  'gdb\\Migration' => 'vendor/base-php/inc/gdb/migration.php',
	  'gdb\\SchemaBuilder' => 'vendor/base-php/inc/gdb/schema_builder.php',
	  'gdb\\TableDefinition' => 'vendor/base-php/inc/gdb/table_definition.php',
	  'RecordNotFoundException' => 'vendor/spitfire/runtime/spitfire.php',
	  'InvalidRecordException' => 'vendor/spitfire/runtime/spitfire.php',
	  'InvalidPropertyException' => 'vendor/spitfire/runtime/spitfire.php',
	  'Errors' => 'vendor/spitfire/runtime/spitfire.php',
	  'SpitfireModel' => 'vendor/spitfire/runtime/spitfire.php',
	);
    // SUPERLOAD-END
    
    if (isset($map[$class])) {
        require $map[$class];
    }
  
}
?>