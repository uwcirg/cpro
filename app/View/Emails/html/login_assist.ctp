<br />

<?php echo __("Hello") . " " .  $patient['User']['first_name'] ; ?>,
<br><br>
<?php echo __("This email will help you log in to ") . SHORT_TITLE . ".";?>
<br><br>
 
<?php
echo $this->Html->link(
    __('Retrieve your login name and password now'),
    $this->Html->url(
        array(
            'controller' => 'users',
            'action' => 'identify',
            $webkey['Webkey']['text']
        ),
        true
    )
);
?>
<?php echo __(" by clicking on the button here:");?>
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
            __('Retrieve login name and password for ') . SHORT_TITLE,
            $this->Html->url(
                array(
                    'controller' => 'users',
                    'action' => 'identify',
                    $webkey['Webkey']['text']
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
echo __("Note: this email will expire in %d hours; if you don't use it in time, simply go to the help page and request login assistance again.", 24);
?>
<br/><br/>

<?php
echo __("Thank you.");
?>


