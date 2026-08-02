<?php

namespace App\Controllers;

use App\Libraries\Sms_lib;

use App\Models\Person;
use CodeIgniter\HTTP\ResponseInterface;

class Messages extends Secure_Controller
{
    private Sms_lib $sms_lib;

    public function __construct()
    {
        parent::__construct('messages');

        $this->sms_lib = new Sms_lib();
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return view('messages/sms');
    }

    /**
     * @param int $person_id
     * @return string
     */
    public function getView(int $person_id = NEW_ENTRY): string
    {
        $person = model(Person::class);
        $info = $person->get_info($person_id);

        foreach (get_object_vars($info) as $property => $value) {
            $info->$property = $value;
        }
        $data['person_info'] = $info;

        return view('messages/form_sms', $data);
    }

    /**
     * @return ResponseInterface
     */
    public function send(): ResponseInterface
    {
        $phone   = $this->request->getPost('phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $message = $this->request->getPost('message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $message_type = $this->request->getPost('message_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $subject = $this->request->getPost('subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'Message from ' . config('OSPOS')->settings['company'];

        $media_url = null;
        $attachment_path = null;
        $attachment = $this->request->getFile('attachment');

        if ($attachment && $attachment->isValid() && !$attachment->hasMoved()) {
            $newName = $attachment->getRandomName();
            // Move file to public/uploads/whatsapp_media
            $attachment->move(FCPATH . 'uploads/whatsapp_media', $newName);
            $attachment_path = FCPATH . 'uploads/whatsapp_media/' . $newName;
            $media_url = base_url('uploads/whatsapp_media/' . $newName);
        }

        if ($message_type === 'whatsapp') {
            $whatsapp = new \App\Libraries\WhatsappLib();
            $provider = $this->config['whatsapp_api_provider'] ?? 'twilio';
            
            if ($provider === 'meta' && !empty($this->config['meta_marketing_template'])) {
                $header_type = null;
                $filename = null;
                if ($media_url) {
                    $ext = strtolower(pathinfo(parse_url($media_url, PHP_URL_PATH), PATHINFO_EXTENSION));
                    $header_type = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'image' : 'document';
                    $filename = basename(parse_url($media_url, PHP_URL_PATH));
                }
                
                $response = $whatsapp->sendTemplate(
                    $phone, 
                    $this->config['meta_marketing_template'],
                    $header_type,
                    $media_url,
                    $filename,
                    $message
                );
            } else {
                $response = $whatsapp->send($phone, $message, $media_url);
            }
        } elseif ($message_type === 'email') {
            $email_lib = new \App\Libraries\Email_lib();
            $emails = array_map('trim', explode(',', $phone));
            $response = true;
            foreach ($emails as $email) {
                if (!empty($email)) {
                    $res = $email_lib->sendEmail($email, $subject, $message, $attachment_path);
                    if (!$res) $response = false;
                }
            }
        } else {
            $response = $this->sms_lib->sendSMS($phone, $message);
        }

        if ($response) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Messages.successfully_sent') . ' ' . esc($phone)]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => lang('Messages.unsuccessfully_sent') . ' ' . esc($phone)]);
        }
    }

    /**
     * Sends an SMS message to a user. Used in app/Views/messages/form_sms.php.
     *
     * @param int $person_id
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function send_form(int $person_id = NEW_ENTRY): ResponseInterface
    {
        $phone   = $this->request->getPost('phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $message = $this->request->getPost('message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $message_type = $this->request->getPost('message_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $subject = $this->request->getPost('subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'Message from ' . config('OSPOS')->settings['company'];

        $media_url = null;
        $attachment_path = null;
        $attachment = $this->request->getFile('attachment');

        if ($attachment && $attachment->isValid() && !$attachment->hasMoved()) {
            $newName = $attachment->getRandomName();
            // Move file to public/uploads/whatsapp_media
            $attachment->move(FCPATH . 'uploads/whatsapp_media', $newName);
            $attachment_path = FCPATH . 'uploads/whatsapp_media/' . $newName;
            $media_url = base_url('uploads/whatsapp_media/' . $newName);
        }

        if ($message_type === 'whatsapp') {
            $whatsapp = new \App\Libraries\WhatsappLib();
            $provider = $this->config['whatsapp_api_provider'] ?? 'twilio';
            
            if ($provider === 'meta' && !empty($this->config['meta_marketing_template'])) {
                $header_type = null;
                $filename = null;
                if ($media_url) {
                    $ext = strtolower(pathinfo(parse_url($media_url, PHP_URL_PATH), PATHINFO_EXTENSION));
                    $header_type = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'image' : 'document';
                    $filename = basename(parse_url($media_url, PHP_URL_PATH));
                }
                
                $response = $whatsapp->sendTemplate(
                    $phone, 
                    $this->config['meta_marketing_template'],
                    $header_type,
                    $media_url,
                    $filename,
                    $message
                );
            } else {
                $response = $whatsapp->send($phone, $message, $media_url);
            }
        } elseif ($message_type === 'email') {
            $email_lib = new \App\Libraries\Email_lib();
            $emails = array_map('trim', explode(',', $phone));
            $response = true;
            foreach ($emails as $email) {
                if (!empty($email)) {
                    $res = $email_lib->sendEmail($email, $subject, $message, $attachment_path);
                    if (!$res) $response = false;
                }
            }
        } else {
            $response = $this->sms_lib->sendSMS($phone, $message);
        }

        if ($response) {
            return $this->response->setJSON([
                'success'   => true,
                'message'   => lang('Messages.successfully_sent') . ' ' . esc($phone),
                'person_id' => $person_id
            ]);
        } else {
            return $this->response->setJSON([
                'success'   => false,
                'message'   => lang('Messages.unsuccessfully_sent') . ' ' . esc($phone),
                'person_id' => NEW_ENTRY
            ]);
        }
    }
}
