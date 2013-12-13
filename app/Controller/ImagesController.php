<?php
/*
    *
    * @copyright Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
    * Controller class for interacting with patient-uploaded images
*/
class ImagesController extends AppController {

    var $uses = array('Patient', 'Image');
    var $components = array('DhairAuth');

    /**
     * Used for embeding or downloading a patient-uploaded image
     * Access is restricted to owner of an image (the patient that uploaded it) and clinic staff
     * @param int $id the UUID of the image
     * @return CakeResponse a response containing the image requested
     * @throws NotFoundException|ForbiddenException
     */
    public function view($id=null){
        $image = $this->Image->findById($id);

        if (!$image)
            throw new NotFoundException('Image not found');

        // Authorization checks
        // Only owner of image and clinic staff may view images
        if (
            !(
                isset($this->user['User']['id']) and
                $image['Image']['patient_id'] == $this->user['User']['id']
            )
            and
            !(
                isset($this->patient['Patient']['id']) and
                $image['Image']['patient_id'] == $this->patient['Patient']['id']
            ) and

            !$this->DhairAuth->checkWhetherUserBelongsToAro(
                $this->user['User']['id'],
                'aroClinicStaff'
            )
        )
            throw new ForbiddenException('You are not authorized to view this image');

        $this->response->file(
            APP.'securedata'.DS.'images'.DS.$image['Image']['id'],
            array('name' => $image['Image']['filename'])
        );

        //Return reponse object to prevent controller from trying to render a view
        return $this->response;
    }
}
?>
