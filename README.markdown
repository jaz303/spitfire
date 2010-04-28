spitfire - a PHP ORM & code-generator
=====================================

&copy; 2010 Jason Frame [ [jason@onehackoranother.com](mailto:jason@onehackoranother.com) / [@jaz303](http://twitter.com/jaz303) ]  
Released under the MIT License.

This is pretty experimental stuff and barely working.

Dependencies
------------

  * [base-php]("http://github.com/jaz303/base-php")
  * [phake]("http://github.com/jaz303/phake")
  * [superload]("http://github.com/jaz303/superload")

Getting up-and-running is a bit of a hassle at the moment. As a priority I'll be writing a script to check out the related projects and knit them together in a fashion suitable for testing/development.

Basic Concepts
--------------

A model class extends `SpitfireModel`, defines its properties, associations and validations via annotations, and contains a placeholder where the generated code should be inserted. A rough example:

    <?php
    /**
     * :model
     * :table = "users"
     * :belongs_to[] = ["account", {"class_name" => "Account"}]
     */
    class User extends SpitfireModel
    {
        /** 
         * :serial
         */
        protected $id;
        
        /**
         * :property
         * :required
         * :validate[] = ["match", "/^[a-z0-9_-]{3,}$/"]
         */
        protected $username;
    
        // SPITFIRE-BEGIN
        // SPITFIRE-END
    }
    ?>
    
And then you compile it all like this:

    <?php
    require 'backend.php';

    $builder = new spitfire\Builder;
    $builder->add_file('../../app/models/TestModel.php');
    $builder->add_file('../../app/models/Business.php');
    $builder->build();
    ?>
