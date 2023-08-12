<?php

/**
 * 
 * copyright @ WereWolf Labs OÜ.
 */

namespace Framework\Http\Session;

use Framework\Http\Session\Session;
use Framework\Configuration\Configuration;

class SessionManager {
    private Configuration $configuration;
    private array $sessions = [];
    private int $expiration;
    private string $hostName;

    public function __construct(Configuration $configuration) {
        $this->configuration = $configuration;
        $this->expiration = $this->configuration->getConfig('sessionExpirationSeconds') ?? 86400;
        $this->hostName = $this->configuration->getConfig('hostName') ?? '';
    }

    /**
     * Returns a random 32 character session id.
     * 
     * @return string
     */
    public function generateSessionId(): string {
        while(true) {
            $randomString = bin2hex(random_bytes(32));

            if (!isset($this->sessions[$randomString])) {
                return $randomString;
            }
        }
    }

    /**
     * Return session object.
     * 
     * @param int $sessionId Session ID.
     * @return Session
     */
    public function getSession(?string $sessionId = null): Session {
        if (isset($this->sessions[$sessionId])) {
            $session = $this->sessions[$sessionId];

            // Check session expiration
            if ((time() - $session->getTimestamp()) > $this->expiration) {
                $this->deleteSession($sessionId);
                $newId = $this->generateSessionId();
                $this->sessions[$newId] = new Session($newId);
                return $this->sessions[$newId];
            }
        } else {
            $newId = $this->generateSessionId();
            $this->sessions[$newId] = new Session($newId);
            return $this->sessions[$newId];
        }

        // 10% chance to regenerate session id to prevent session fixation attacks.
        if (random_int(1, 10) === 1) {
            $this->deleteSession($sessionId);
            $newId = $this->generateSessionId();
            $this->sessions[$newId] = new Session($newId, $session->getData());
            return $this->sessions[$newId];
        }

        // Update session timestamp
        $session = $this->sessions[$sessionId];
        $session->updateTimestamp();
        return $session;
    }

    public function getSessions(): array {
        return $this->sessions;
    }

    public function deleteSession(string $sessionId): void {
        if (isset($this->sessions[$sessionId])) {
            unset($this->sessions[$sessionId]);
        }
    }

    public function getExpirationSeconds(): int {
        return $this->expiration;
    }

    public function setExpirationSeconds(int $seconds): void {
        $this->expiration = $seconds;
    }
}