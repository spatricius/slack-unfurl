<?php

namespace App\Controller;

use App\Message\LinkSharedMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    #[Route('/unfurl/', name: 'unfurl')]
    public function respondAction(Request $request, MessageBusInterface $bus, $slackAppId, $slackRequestToken): Response
    {
        $requestBody = $request->getContent();
        try {
            $requestJson = json_decode($requestBody, false);
        } catch
        (\Throwable $e) {
        }

        // check token
        if (empty($requestJson?->token) || $requestJson?->token !== $slackRequestToken) {
            return new Response('<h1>nope.</h1>', 404);
        }

        // check api challenge (one-time)
        if (!empty($requestJson?->challenge)) {
            return $this->json(array(
                'challenge' => $requestJson->challenge,
            ));
        }

        if (empty($requestJson?->api_app_id) || $requestJson?->api_app_id !== $slackAppId){
            return new Response('<h1>bad app id.</h1>', 404);
        }

        // unfurl
        if ($requestJson?->event?->type === 'link_shared') {
            $bus->dispatch(new LinkSharedMessage($requestJson->event));

            // tell slack the event will be processed
            return new Response('ok');
        }

        return new Response('<h1>bad event type.</h1>', 404);
    }
}