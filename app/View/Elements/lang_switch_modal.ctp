<?php
// Displaying opposite language than what's currently showing up
if ( $this->Session->read('Config.language') == 'en_US' || $this->Session->read('Config.language') == '' ) {
    $currentLang = 'en';
} else {
    $currentLang = 'es';
}
?>
<div id="langSwitchModal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Change language / Cambiar el idioma</h3>
  </div>
  <div class="modal-body">

      <br />
      <?php if ($currentLang == 'en') { ?>
      <p>Para confirmar que desea cambiar a español, por favor haga clic en el botón de abajo.</p>           
      <p><small>Accidentally clicked the language change button? If you do not 
              want to switch to view this site in Spanish, click the "Cancel" button below.</small></p>
      <?php } else { ?>             
      <p>To confirm that you would like to switch to English, please click the button below.</p>
      <p><small>Accidentalmente click en el botón de cambio de idioma? Si no quiere cambiar para ver este sitio en español, haga clic en el botón "Cancelar" de abajo.</small></p>
      <?php } ?>              
      
  </div>
  <div class="modal-footer">
    <button id="langSwitchConfirm" class="btn btn-large btn-primary" name="<?= ($currentLang == 'en') ? 'es_MX' : 'en_US' ?>">
        <?= ($currentLang == 'en') ? "Cambiar el idioma a español" : "Change language to English"; ?>
    </button>&nbsp;
    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
  </div>
</div>