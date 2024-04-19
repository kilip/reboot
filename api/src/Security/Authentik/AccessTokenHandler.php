<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Security\Authentik;

use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\JWSLoader;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\AccessToken\Oidc\Exception\MissingClaimException;
use Symfony\Component\Security\Http\AccessToken\Oidc\OidcTrait;
use Symfony\Component\Security\Http\Authenticator\FallbackUserLoader;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    use OidcTrait;

    public function __construct(
        #[Autowire('@cache.app')]
        private readonly CacheInterface $cache,

        #[Autowire('@jose.jws_loader.oidc')]
        private readonly JWSLoader $jwsLoader,

        #[Autowire('%env(OIDC_CONFIGURATION_URL)%')]
        private readonly string $oidcConfigurationUrl,

        private readonly HttpClientInterface $securityAuthorizationClient,
        private readonly LoggerInterface $logger,
        private readonly string $claim = 'email',
        private readonly int $ttl = 600,
    ) {
    }

    public function getUserBadgeFrom(
        #[\SensitiveParameter] string $accessToken
    ): UserBadge {
        $oidcConfiguration = $this->createOidcConfiguration();
        $keyset = $this->createKeyset($oidcConfiguration);
        $this->logger->notice($accessToken);
        try {
            $signature = null;
            $jws = $this->jwsLoader->loadAndVerifyWithKeySet(
                token: $accessToken,
                keyset: $keyset,
                signature: $signature,
            );
            $claims = json_decode($jws->getPayload(), true);
            if (empty($claims[$this->claim])) {
                throw new MissingClaimException(sprintf('"%s" claim not found.', $this->claim));
            }

            return new UserBadge(
                $claims[$this->claim],
                new FallbackUserLoader(fn () => $this->createUser($claims)), $claims);
        } catch (\Throwable $e) {
            $this->logger->error('An error occurred while decoding and validating the token.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new BadCredentialsException('Invalid credentials.', $e->getCode(), $e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function createOidcConfiguration(): array
    {
        try {
            $cache = $this->cache->get('oidc.configuration', function (ItemInterface $item): string {
                $item->expiresAfter($this->ttl);
                $response = $this->securityAuthorizationClient->request('GET', $this->oidcConfigurationUrl);

                return $response->getContent();
            });
            $this->logger->notice('oidc configuration', json_decode($cache, true));

            return json_decode($cache, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $this->logger->error('An error occurred while requesting OIDC configuration.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new BadCredentialsException('Invalid credentials.', $e->getCode(), $e);
        }
    }

    /**
     * @param array<string,mixed> $oidcConfiguration
     */
    private function createKeyset(array $oidcConfiguration): JWKSet
    {
        try {
            $cache = $this->cache->get('authentik.jwks', function (ItemInterface $item) use ($oidcConfiguration): string {
                $item->expiresAfter($this->ttl);
                $response = $this->securityAuthorizationClient->request('GET', $oidcConfiguration['jwks_uri']);
                $keys = array_filter($response->toArray()['keys'], static fn (array $key) => 'sig' === $key['use']);

                return json_encode(['keys' => $keys]);
            });

            return JWKSet::createFromJson($cache);
        } catch (\Throwable $e) {
            $this->logger->error('An error occurred while requesting OIDC certs.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new BadCredentialsException('Invalid credentials.', $e->getCode(), $e);
        }
    }
}
