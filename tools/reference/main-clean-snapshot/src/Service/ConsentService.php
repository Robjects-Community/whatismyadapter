<?php
declare(strict_types=1);

namespace App\Service;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * ConsentService handles cookie consent and session management logic.
 *
 * This service extracts business logic from controllers to provide
 * reusable consent handling functionality across the application.
 */
class ConsentService
{
    /**
     * Gets consent data for view variables.
     *
     * This method handles the complete consent data flow:
     * - Ensures session is started
     * - Retrieves session ID
     * - Processes consent cookie if present
     * - Returns data ready for view consumption
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object
     * @return array Array containing sessionId and consentData for view
     * @throws \RuntimeException If session cannot be started
     * @throws \InvalidArgumentException If cookie data cannot be decoded
     */
    public function getConsentData(ServerRequestInterface $request): array
    {
        $sessionId = $this->startSessionIfNeeded($request);
        $consentData = $this->handleConsentCookie($request);

        return compact('sessionId', 'consentData');
    }

    /**
     * Handles consent cookie parsing and validation.
     *
     * Retrieves the consent cookie from the request and decodes it.
     * Returns null if no cookie is present or if decoding fails.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object
     * @return array|null Decoded consent data or null if not present/invalid
     * @throws \InvalidArgumentException If cookie data cannot be decoded
     */
    public function handleConsentCookie(ServerRequestInterface $request): ?array
    {
        $consentCookie = $request->getCookie('consent_cookie');

        if (!$consentCookie) {
            return null;
        }

        $consentData = json_decode($consentCookie, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid consent cookie data: ' . json_last_error_msg());
        }

        return $consentData;
    }

    /**
     * Starts session if not already started and returns session ID.
     *
     * Ensures the session is properly initialized before accessing
     * session data. This is essential for consent tracking.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object
     * @return string The session ID
     * @throws \RuntimeException If session cannot be started
     */
    public function startSessionIfNeeded(ServerRequestInterface $request): string
    {
        $session = $request->getSession();

        if (!$session->started()) {
            if (!$session->start()) {
                throw new RuntimeException('Unable to start session for consent tracking');
            }
        }

        return $session->id();
    }
}
