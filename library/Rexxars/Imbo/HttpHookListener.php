<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Rexxars\Imbo;

use Imbo\EventManager\EventInterface,
    Guzzle\Http\Client as HttpClient;

/**
 * HTTP hook event listener
 *
 * This event listener will listen to configured events and perform HTTP POST-requests
 * at configured endpoints.
 *
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @package Event\Listeners
 */
class HttpHookListener implements ListenerInterface {
    /**
     * Event mappings. Keys represent event names, value is an array of endpoints to hit.
     *
     * @var array
     */
    private $params = [];

    /**
     * Holds the queued HTTP requests (all requests will be sent in bulk)
     *
     * @var array
     */
    private $requestQueue = [];

    /**
     * Guzzle HTTP client instance
     *
     * @var Guzzle\Http\Client
     */
    private $httpClient;

    /**
     * Class constructor
     *
     * @param array $params Parameters for the listener
     */
    public function __construct(array $params = array()) {
        $this->params = array_replace($this->params, $params);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return [
            'route.match' => 'subscribe',
            'response.send' => ['sendHttpRequests' => 5],
        ];
    }

    /**
     * Subscribe to events based on configuration parameters
     *
     * @param EventInterface $event The event instance
     */
    public function subscribe(EventInterface $event) {
        $events = array_fill_keys(array_keys($this->params), 'queueRequest');

        $manager = $event->getManager();
        $manager->addCallbacks($event->getHandler(), $events);
    }

    /**
     * Handle other requests
     *
     * @param EventInterface $event The event instance
     */
    public function queueRequest(EventInterface $event) {
        $request = $event->getRequest();
        $eventName = $event->getName();
        $urls = (array) $this->params[$eventName];

        $data = [
            'event' => $eventName,
            'url' => $request->getRawUri(),
            'imageIdentifier' => $request->getImageIdentifier(),
            'publicKey' => $request->getPublicKey(),
        ];

        foreach ($urls as $url) {
            $this->requestQueue[] = $this->getHttpClient()->post($url, null, $data);
        }
    }

    /**
     * Send all the queued HTTP requests (if any)
     *
     * @param EventInterface $event The event instance
     */
    public function sendHttpRequests(EventInterface $event) {
        if (empty($this->requestQueue)) {
            return;
        }

        $this->getHttpClient()->send($this->requestQueue);
    }

    /**
     * Get the HTTP client to use for outgoing requests
     *
     * @return Guzzle\Http\Client
     */
    protected function getHttpClient() {
        if (!$this->httpClient) {
            $this->httpClient = new HttpClient();
        }

        return $this->httpClient;
    }

}
