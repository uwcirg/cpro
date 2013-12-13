<br />

Hello <?php echo $patient['User']['first_name'] ; ?>,<br><br>

This email is to notify you that your doctor’s office has registered you for the
 <?php echo EMAIL_TITLE ?>.<br><br>
 
If you would like to use this program, 
<?php
echo $this->Html->link(
    'create an account now',
    $this->Html->url(
        array(
            'controller' => 'users',
            'action' => 'selfRegister',
            $user['User']['webkey']
        ),
        true
    )
);
?>
 by clicking on the button here:<br><br>

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
            'Register for ESRA-C',
            $this->Html->url(
                array(
                    'controller' => 'users',
                    'action' => 'selfRegister',
                    $user['User']['webkey']
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
If you're not interested in using <?php echo SHORT_TITLE; ?>, 
<?php
echo $this->Html->link(
    'click here to remove yourself',
    $this->Html->url(
        array(
            'controller' => 'patients',
            'action' => 'optOut',
            $user['User']['webkey']
        ),
        true
    )
);
?>
 from the program.<br><br>
 
 Thank you.

