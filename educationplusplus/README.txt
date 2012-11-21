How to Import this Module into your copy of Moodle
==================================================

1 Place the module folder ("educationplusplus" - make sure it is all lowercase)
  into the /mod folder of the moodle directory.

2 Go to Settings > Site Administration > Development > XMLDB editor
  and modify the module's tables.

3 Visit Settings > Site Administration > Notifications, you should find
  the module's tables successfully created

4 Go to Site Administration > Plugins > Activity modules > Manage activities
  and you should find that this educationplusplus has been added to the list of
  installed modules.

5 You may now proceed to run your own code in an attempt to develop
  your module. You will probably want to modify mod_form.php and view.php
  as a first step. Check db/access.php to add capabilities.