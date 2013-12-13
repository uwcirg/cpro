<?php

class TestRandomizationShell  extends AppShell {
    var $uses = array('User', 'Patient');

    function main() {
        $testCount = 1;
        for ($i=0; $i < $testCount; $i++){
            $this->Patient->create();
            $patient = $this->Patient->save(array(
                'Patient'=>array(
                    'MRN' => 'random_t',
                    'test_flag' => 0,
            )));

            // $patient = $this->Patient->findById(1893);

            $this->User->create();
            $user = $this->User->save(array(
                'id' => $patient['Patient']['id'],
                'username' => 'random_t',
                'first_name' => 'random_t',
                'last_name' => 'random_t',
                'clinic_id' => 3,
            ), array('validate'=>false));

            $this->Patient->setToParticipantAndRandomize(
                $patient['Patient']['id'],
                'primary',
                $patient['Patient']['test_flag']
            );

            $patient = $this->Patient->findById($patient['Patient']['id']);
            // print_r($patient);
            sleep(1);
        }
    }
}

?>