# Context

Context allows you to manage contextual conditions and reactions for different
portions of your site. You can think of each context as representing a "section"
of your site. For each context, you can choose the conditions that trigger this
context to be active and choose different aspects of Drupal that should react
to this active context.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/context).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/context).


## Table of contents

- Requirements
- Conditions
- Reactions
- Installation
- Configuration
- Maintainers


## Requirements

This module requires no modules outside of Drupal core.


# Conditions

Context for Drupal 8 uses the built in condition plugins supplied by Drupal
through the [Plugin API](https://www.drupal.org/developing/api/8/plugins). 
So any conditional plug-ins supplied by other modules can also be used with
context.


# Reactions

Reactions for the context module are defined trough the new Drupal 8 
[Plugin API](https://www.drupal.org/developing/api/8/plugins).

The context module defines a plugin type named Context Reaction that you can
extend when creating your own plugins.

A context reaction requires a configuration form and execute method. The 
execution of the plugin is also something that will have to be handled by the
author of the reaction.


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

1. Navigate to Administration > Extend and enable the module and the
   submodule Context UI.
2. Navigate to Administration > Structure > Context to associate menus,
   views, blocks, etc. with different contexts.
3. Select "Add context" to add general details for a new context. Save.
4. Add conditions. When there are no added conditions the context will be
   considered sitewide.
5. Add reactions.
6. Save and continue.


## Maintainers

- Bostjan Kovac - [boshtian](https://www.drupal.org/u/boshtian)
- Colan Schwartz - [colan](https://www.drupal.org/u/colan)
- Frank Febbraro - [febbraro](https://www.drupal.org/u/febbraro)
- Chris Johnson - [tekante](https://www.drupal.org/u/tekante)
- Alex Barth - [alex_b](https://www.drupal.org/u/alex_b)
- emanaton - [emanaton](https://www.drupal.org/u/emanaton)
- Hunter Fox - [hefox](https://www.drupal.org/u/hefox)
- Jeff Miccolis - [jmiccolis](https://www.drupal.org/user/31731)
- Nedjo Rogers - [nedjo](https://www.drupal.org/u/nedjo)
- Patrick Settle - [patricksettle](https://www.drupal.org/u/patricksettle)
- Paulo Henrique Cota Starling - [paulocs](https://www.drupal.org/u/paulocs)
- Steven Jones - [Steven Jones](https://www.drupal.org/u/steven-jones)
- yhahn - [yhahn](https://www.drupal.org/user/264833)
- Yonas Yanfa - [fizk](https://www.drupal.org/u/fizk)
- Christoffer Palm - [NormySan](https://www.drupal.org/u/normysan)
