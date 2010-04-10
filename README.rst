======================================================
Jinja/Django-like Template Inheritance for CodeIgniter
======================================================

Description
-----------

This CodeIgniter-helper provides a stripped-down flavour of Jinja/Django -
specifically, only inheritance, which we know as ``{% extends %}`` and
``{% block %}`` in Jinja/Django.

Unlike Jinja/Django, the template is not run through a lexer. Block markers and
the ``extends`` directive are all specified in PHP code, instead of
``'{%'/'%}'`` tags, reducing possible performance hits.

Usage
-----

1. Download/Checkout a copy of the repo into the ``libraries/`` folder of either
   your application directory or core CodeIgniter directory.

2. In your ``index.php`` or applications config, add the following constants::

     // Name of folder that this repo is in.
     define('JINJA_INHERITANCE_DIRNAME', 'codeigniter-jinja-inheritance');
     // Full path to the repo.
     define('JINJA_INHERITANCE_PATH', APPPATH.'libraries/'.JINJA_INHERITANCE_DIRNAME);

   Change ``APPPATH`` to ``BASEPATH`` in the second ``define()`` if you've
   placed the repo in the core CodeIgniter folder.

3. In your controller, load your view as you normally would::


     function a_func() {
       $this->load->library(JINJA_INHERITANCE_DIRNAME.'/ji_loader', NULL, 'ji_load');
       $this->ji_load->view('a_view');
     }

   Modify the 3 argument to ``$this->load->library()`` to change the object
   name which the new ``Loader`` library is accessed from.

Tags/Functions
--------------

The three template-inheritance tags in Jinja/Django are provided.

These methods are available in views via ``$this``, as we've overriden
CodeIgniter's default ``Loader`` implementation.

 * ``extends_view('*view_name*')``

   Jinja/Django: ``{% extends %}``

   Specify *view_name* as you would a CodeIgniter view.

 * ``start_block('*block_name*')``

   Jinja/Django: ``{% block %}``

 * ``end_block(['*block_name*'])``

   Jinja/Django: ``{% endblock %}``

Examples
--------

Simple Inheritance
^^^^^^^^^^^^^^^^^^

base.php::

  A heading.

  <?php $this->start_block("content"); ?>
  Content goes here.
  <?php $this->end_block(); /* block name is optional */?>


index.php::

  <?php $this->extends_view("base"); ?>

  <?php $this->start_block("content"); ?>
  Welcome to foo.com!
  <?php $this->end_block("content"); /* block name is optional */?>

result::

  A heading.

  Welcome to foo.com!

Nested Blocks
^^^^^^^^^^^^^

base.php::

  A heading.

  <?php $this->start_block("content"); ?>

  <?php $this->start_block("blurb"); ?>
  - Welcome to foo.com - where you'll find all things baz.
  <?php $this->end_block("blurb");?>

  Welcome to foo.com!
  <?php $this->end_block(); /* block name is optional */?>

two-column.php::

  <?php $this->extends_view("base"); ?>

  <?php $this->start_block("content"); ?>

    <?php $this->start_block("blurb"); ?>
    <?php $this->end_block("blurb");?>

    <?php
    // Note: this block wasn't defined in base; it will show up, as expected.
    $this->start_block("text");
    ?>
    <?php $this->end_block("text");?>

  <?php $this->end_block(); ?>


index.php::

  <?php $this->extends_view("two-column"); ?>

  <?php $this->start_block("blurb"); ?>
  - Thing are all baz here.
  <?php $this->end_block("blurb");?>

  <?php $this->start_block("text"); ?>
  This is the baz you've been waiting for.
  <?php $this->end_block("text");?>

result (extraneous newlines eschewed for presentation purposes)::

  A heading.

  - Thing are all baz here.

  This is the baz you've been waiting for.

License
-------

Copyright (C) 2010, Tay Ray Chuan
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
      this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.
    * The name of the author may not be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
