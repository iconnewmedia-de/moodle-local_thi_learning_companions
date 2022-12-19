<?php

$string['messageprovider:group_join_requested'] = 'Group Join request';
$string['messageprovider:appointed_to_admin'] = 'Appointed to admin';
$string['messageprovider:group_join_accepted'] = 'Group Join accepted';
$string['messageprovider:group_join_denied'] = 'Group Join denied';
$string['messageprovider:invited_to_group'] = 'Invited to group';

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

### Group Join Accepted
$string['message_group_join_accepted_small'] = 'Your request to join group {$a} has been accepted';
$string['message_group_join_accepted_subject'] = 'Group Join request accepted';
$string['message_group_join_accepted_body'] = 'Hello {$a->receivername},

{$a->sendername} has accepted your request to join group "{$a->groupname}".';
$string['message_group_join_accepted_body_html'] = '<h4>Hello {$a->receivername},</h4>
<p>
{$a->sendername} has accepted your request to join group "{$a->groupname}".</p>';

### Group Join Denied
$string['message_group_join_denied_small'] = 'Your request to join group {$a} has been denied';
$string['message_group_join_denied_subject'] = 'Group Join request denied';
$string['message_group_join_denied_body'] = 'Hello {$a->receivername},

{$a->sendername} has denied your request to join group "{$a->groupname}".';
$string['message_group_join_denied_body_html'] = '<h4>Hello {$a->receivername},</h4>
<p>
{$a->sendername} has denied your request to join group "{$a->groupname}".</p>';

### Group Inivted
$string['message_invited_to_group_small'] = 'You were invited into group {$a}';
$string['message_invited_to_group_subject'] = 'Group Invite';
$string['message_invited_to_group_body'] = 'Hello {$a->receivername},

{$a->sendername} has invited you into the group "{$a->groupname}".';
$string['message_invited_to_group_body_html'] = '<h4>Hello {$a->receivername},</h4>
<p>
{$a->sendername} has invited you into the group "{$a->groupname}".</p>';
