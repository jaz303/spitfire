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

Hacking
-------

  * Clone the main `spitfire` repo, and any of its dependencies you're going to want to change
  * Copy the `dev-bootstrap` script from the `spitfire` project root to your filesystem
  * From the console, invoke `./dev-bootstrap your_github_username target_dir`
  * A skeleton project will be set up in `target_dir`, preferring any dependencies you've cloned (for read/write access), and falling back to the original read-only versions.

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
