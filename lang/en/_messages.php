<?php

$string['messageprovider:group_join_requested'] = 'Group Join request';
$string['messageprovider:appointed_to_admin'] = 'Appointed to admin';

### Group Join Requested
$string['message_group_join_requested_small'] = 'New request to join group {$a}';
$string['message_group_join_requested_subject'] = 'Group Join request';
$string['message_group_join_requested_body'] = 'Hello {$a->receivername},

{$a->sendername} has requested to join your group "{$a->groupname}".
Please visit the group page to accept or decline the request.';
$string['message_group_join_requested_body_html'] = '<h4>Hello {$a->receivername},</h4>
<p>
{$a->sendername} has requested to join your group "{$a->groupname}".<br>
Please visit the group page to accept or decline the request.
</p>';


### Appointed to Admin
$string['message_appointed_to_admin_small'] = 'You are now the admin of group {$a}';
$string['message_appointed_to_admin_subject'] = 'Appointed to admin';
$string['message_appointed_to_admin_body'] = 'Hello {$a->receivername},

{$a->sendername} has appointed you to admin of group "{$a->groupname}".';
$string['message_appointed_to_admin_body_html'] = '<h4>Hello {$a->receivername},</h4>
<p>
{$a->sendername} has appointed you to admin of group "{$a->groupname}".</p>';
