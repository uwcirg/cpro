<br />
<?php

// Determine whether to use anonymous access URL or not
if ($webkey)
    $linkSettings = array(
        'controller' => 'users',
        'action' => 'identify',
        $webkey
    );
else
    $linkSettings = array(
        'controller' => 'users',
        'action' => 'login'
    );

echo String::insert(
    __('Dear Mr. :last_name'),
    array('last_name' => $patient['User']['last_name'])
);
?>
,<br><br>

<p>
<?php
echo __('About 6 months ago, you joined the P3P program. We are contacting you now for the 6-month follow-up, which is an important part of our understanding the P3P results.');
?>
</p>
<p>
<?php
echo String::insert(
    __('We hope you can take a short time to answer the 6-month follow-up questionnaire. Please :link_startvisit the P3P website for your 6-month follow-up now:link_end, or before :end_date'),
    array(
        'link_start' =>
            '<a href="'.$this->Html->url(
                $linkSettings,
                true
            ).'" >',
        'link_end' => '</a>',
        'end_date' => $patientProjectsStates[P3P_6_MO_FU_PROJECT]->availableDateRangeEnd,
    )
);
?>
</p>

<p>
<?php
// Only show this for unregistered patients, who will be forced to register
if (!$user['User']['registered'])
    echo __('You will be asked to register for the website, if you have not already done so. This is for security of any information that you may put into the website.');
?>
</p>

<br><br>

<?php
// Button link based on:
// http://www.developerdrive.com/2012/05/creating-bulletproof-email-buttons/
?>
<table>
  <tr>
    <td align="center" width="300" bgcolor="#0088cc" style="background: #0088cc;
        -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px;
        color: #fff; font-weight: bold; text-decoration: none; padding: 6px;
        font-family: Helvetica, Arial, sans-serif; display: block;">
        <?php
        echo $this->Html->link(
            __('Go To P3P'),
            $this->Html->url(
                $linkSettings,
                true
            ),
            array('style' => 'color: #fff; text-decoration: none; display: block; font-size: 16px; width: 300px')
        );
        ?>
    </td>
  </tr>
</table>
<br><br>

<?php
echo __('Click on the button above or follow this link (or copy the address into your Web browser) to enter the P3P site:');
?>
<br />
<?php
echo $this->Html->link(
    $this->Html->url(
        $linkSettings,
        true
    )
);
?>
<br><br>

<?php
echo __('If you have any questions, please contact:')
?>
<br /><br />

<?php
echo "{$user['Clinic']['support_name']}<br />";
echo "{$user['Clinic']['support_phone']}<br />";
echo "<a href='mailto:{$user['Clinic']['support_email']}?subject=".__('P3P Support Question')."'>{$user['Clinic']['support_email']}</a><br />";
echo "{$user['Clinic']['friendly_name']}<br />";
?>
<br /><br />
<?php
echo __('Please use this contact information <strong>only for the P3P program</strong>, and do not send private medical information by email.');

?>
