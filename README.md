Podium
======

[![Join the chat at https://gitter.im/bizley/yii2-podium](https://badges.gitter.im/bizley/yii2-podium.svg)](https://gitter.im/bizley/yii2-podium?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Latest Stable Version](https://img.shields.io/packagist/v/bizley/podium.svg)](https://packagist.org/packages/bizley/podium)
[![Total Downloads](https://img.shields.io/packagist/dt/bizley/podium.svg)](https://packagist.org/packages/bizley/podium)
[![License](https://img.shields.io/packagist/l/bizley/podium.svg)](https://github.com/bizley/yii2-podium/blob/master/LICENSE)
[![Code Climate](https://codeclimate.com/github/bizley/yii2-podium/badges/gpa.svg)](https://codeclimate.com/github/bizley/yii2-podium)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

Yii 2 forum module
------------------

This extension allows you to add forum to your app.

Features
--------

- [Bootstrap](http://getbootstrap.com) responsive layout
- [Quill](https://github.com/bizley/yii2-quill) WYSIWYG editor
- [CodeMirror](https://codemirror.net/) Markdown editor
- Supports Cache, Formatter and Connection components
- Built-in user identity handling (+supports inherited identity)
- Built-in RBAC component (+supports inherited RBAC)
- Console mail queue handling
- Avatars uploading (+supports Gravatars)
- Built-in user messages component
- English, Polish, Russian, and Spanish translation (+Japanese coming up)
- Available with polls system

Installation & configuration
----------------------------

Follow instructions at [Podium wiki](https://github.com/bizley/yii2-podium/wiki).

> Warning: This is BETA version of Podium.
> Any part of this module can change without warning.
> Please report all issues and suggestions [here](https://github.com/bizley/yii2-podium/issues).

Demo
----

Podium Demo is available at [http://bizley.pl/podium](http://bizley.pl/podium).

Screenshots
-----------

Main page
![Podium main page](https://bizley.github.io/podium/podium1.png)

Member view
![Podium member view](https://bizley.github.io/podium/podium2.png)

Messages inbox
![Podium messages inbox](https://bizley.github.io/podium/podium3.png)

Thread view
![Podium thread view](https://bizley.github.io/podium/podium4.png)

Discussion
----------

Join [Gitter](https://gitter.im/bizley/yii2-podium) channel.

Tests
-----

For Codeception tests run:

    composer exec -v -- codecept -c vendor/bizley/podium run
