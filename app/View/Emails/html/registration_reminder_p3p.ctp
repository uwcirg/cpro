<br />
<?php
echo String::insert(
    __('Dear Mr. :last_name'),
    array('last_name' => $patient['User']['last_name'])
);
?>
,<br><br>

<?php
echo String::insert(
    __('Thank you for agreeing to take part in the P3P program. As promised, this email provides the Web address for you to :link_start start using the program.:link_end'),
    array(
        'link_start' =>
            '<a href="'.$this->Html->url(
                array(
                    'controller' => 'users',
                    'action' => 'identify',
                    $webkey,
                ),
                true
            ).'" >',
        'link_end' => '</a>',
    )
);
?>
<br><br>

<?php
$apptEmail = strtotime($patient['Appointment'][0]['datetime']);
echo String::insert(
    __('<strong>You can only participate in the program if you use the website before your appointment on :appointment_date at :appointment_time</strong>, so please go to P3P now:'),
    array(
        'appointment_date' => date('l, F j', $apptEmail),
        'appointment_time' => date('g:ia', $apptEmail),
    )
);
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
                array(
                    'controller' => 'users',
                    'action' => 'identify',
                    $webkey
                ),
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
        array(
            'controller' => 'users',
            'action' => 'identify',
            $webkey
        ),
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