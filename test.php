<?php
require 'backend.php';

$builder = new spitfire\Builder;
$builder->add_file('../../app/models/TestModel.php');
$builder->add_file('../../app/models/Business.php');
$builder->build();

?>