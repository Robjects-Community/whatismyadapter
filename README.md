# WhatIsMyAdaptor
Webapp project containing useful adapter (C, USB, etc.,) all in one cohesive place.

Aiming to answer the common problem of trying to find the connection adapter for your specific needs with specific links without having to go through all the hassle.

## Table of Contents

- [WhatIsMyAdaptor](#whatismyadaptor)
  - [Table of Contents](#table-of-contents)
  - [Instructions](#instructions)
  - [Contributing to Projects](#contributing-to-projects)
  - [License](#license)


## Instructions

Software setup:
1) Install docker
2) Clone repo
3) cd into folder
4) `docker-compose up -d`
5) terminal in the container and run `bin/cake migrations migrate` to migrate the DB
6) terminal in the container and run `bin/cake migrations seed` to seed the DB

<!-- 1. Setup of RPi Image -->
<!-- TODO: Add detailed instructions for setting up the RPi image -->

<!-- 2. Explanation of Project Contents and Structure -->
<!-- TODO: Provide an explanation of the project contents and structure -->
CakePHP Folder Structure
########################

After you've downloaded the CakePHP application skeleton, there are a few top
level folders you should see:

- The *bin* folder holds the Cake console executables.
- The *config* folder holds the :doc:`/development/configuration` files
  CakePHP uses. Database connection details, bootstrapping, core configuration files
  and more should be stored here.
- The *plugins* folder is where the :doc:`/plugins` your application uses are stored.
- The *logs* folder normally contains your log files, depending on your log
  configuration.
- The *src* folder will be where your application’s source files will be placed.
- The *templates* folder has presentational files placed here:
  elements, error pages, layouts, and view template files.
- The *resources* folder has sub folder for various types of resource files.
  The *locales* sub folder stores language files for internationalization.
- The *tests* folder will be where you put the test cases for your application.
- The *tmp* folder is where CakePHP stores temporary data. The actual data it
  stores depends on how you have CakePHP configured, but this folder
  is usually used to store translation messages, model descriptions and sometimes
  session information.
- The *vendor* folder is where CakePHP and other application dependencies will
  be installed by `Composer <https://getcomposer.org>`_. Editing these files is not
  advised, as Composer will overwrite your changes next time you update.
- The *webroot* directory is the public document root of your application. It
  contains all the files you want to be publicly reachable.

  Make sure that the *tmp* and *logs* folders exist and are writable,
  otherwise the performance of your application will be severely
  impacted. In debug mode, CakePHP will warn you, if these directories are not
  writable.

The src Folder
==============

CakePHP's *src* folder is where you will do most of your application
development. Let's look a little closer at the folders inside
*src*.

Command
    Contains your application's console commands. See
    :doc:`/console-commands/commands` to learn more.
Console
    Contains the installation script executed by Composer.
Controller
    Contains your application's :doc:`/controllers` and their components.
Middleware
    Stores any :doc:`/controllers/middleware` for your application.
Model
    Contains your application's tables, entities and behaviors.
View
    Presentational classes are placed here: views, cells, helpers.


<!-- 3. Development Setup of Project -->
<!-- TODO: Describe the development setup of the project -->

<!-- 4. Running the Code for Local Development -->
<!-- TODO: Explain how to run the code for local development -->

<!-- 5. Putting the Website Online -->
<!-- TODO: Outline the steps for putting the website online -->

## Contributing to Projects

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
