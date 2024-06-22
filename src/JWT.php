<?php

namespace yangchao\jwt;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use yangchao\jwt\exception\JWTServerException;
use InvalidArgumentException;
use yangchao\jwt\exception\JWTVerifyException;

class JWT
{
    protected Config $config;
    protected Payload|null $payload;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->payload = new Payload($this->config);
    }

    public function payload()
    {
        if (!$this->payload) {
            JWTServerException::throwException('payload is null');
        }
        return $this->payload;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setAppStore(string $store)
    {
        $this->config->setAppStore($store);
    }

    public function make(array $data = [], bool $isRefreshToken = false): string
    {
        return $this->encode($this->payload->customClaims($data, $isRefreshToken)->getPayload(),
            $this->config->getSignerPrivateKey($isRefreshToken),
            $this->config->getSigner());
    }

    private function encode($payload, $key, $alg)
    {
        return FirebaseJWT::encode($payload, $key, $alg);
    }

    public function verify(string $token, bool $refresh = false)
    {
        $this->payload->setPayload($this->parse($token, $refresh));
        if ($this->issetBlackList($this->payload->getJti())) {
            JWTVerifyException::throwException('token存在黑名单');
        }
        if ($this->payload->getIss() !== $this->config->getIis()) {
            JWTVerifyException::throwException('签发主体不一致');
        }
        if ($this->payload->getStore() !== $this->config->getAppStore()) {
            JWTVerifyException::throwException('签发应用不一致');
        }
        //验证单设备登录
        if ($this->config->getIsSingleDevice()) {
            if ($this->payload->getDeviceEncode() !== $this->payload->getDevice()) {
                JWTVerifyException::throwException('身份已在其他地方占用');
            };
        }
        return $this->payload->getClaims();
    }

    public function parse(string $token, bool $refresh = false): array
    {
        try {
            FirebaseJWT::$leeway = $this->config->getLeeway();
            $pare = FirebaseJWT::decode($token, new Key($this->config->getSignerPublicKey($refresh), $this->config->getSigner()));
            return json_decode(json_encode($pare, JSON_UNESCAPED_UNICODE), true);
        } catch (InvalidArgumentException) {
            JWTVerifyException::throwException('身份验证令牌不可用');
        } catch (SignatureInvalidException) {
            JWTVerifyException::throwException('身份验证令牌无效');
        } catch (BeforeValidException) {
            JWTVerifyException::throwException('身份验证令牌尚未生效');
        } catch (ExpiredException $exception) {
            JWTVerifyException::throwException('身份验证会话已过期');
        } catch (\Exception $e) {
            JWTVerifyException::throwException($e->getMessage());
        }
    }

    public function refresh($token): string
    {
        $isRefreshToken = !$this->config->getRefreshDisable();
        $claims = $this->verify($token, $isRefreshToken);
        //加入黑名单
        $expireTime = $this->payload->getExpireTime() - time();
        $this->addBlacklistServer($this->payload->getJti(), $expireTime);
        $this->addBlacklistServer($this->payload->getFromJti(), $expireTime);
        //生成新的Token
        return $this->make($claims);
    }

    public function addBlacklist($token)
    {
        $this->verify($token);
        return $this->addBlacklistServer($this->payload->getJti(), $this->payload->getExpireTime() - time());
    }

    public function removeBlacklist($token)
    {
        $this->verify($token);
        return $this->removeBlacklistServer($this->payload->getJti());
    }

    public function issetBlackList($jti): bool
    {
        return boolval($this->getBlackList($jti));
    }

    private function addBlacklistServer($jti, $expireTime)
    {
        return BackList::instance($this->config)->add($jti, $expireTime);
    }

    private function removeBlacklistServer($jti)
    {
        return BackList::instance($this->config)->remove($jti);
    }

    private function getBlackList($jti)
    {
        return BackList::instance($this->config)->get($jti);
    }
}