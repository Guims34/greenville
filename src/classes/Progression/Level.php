<?php
namespace App\Classes\Progression;

class Level {
    private int $level;
    private int $xpRequired;
    private int $coinsReward;
    private int $premiumCoinsReward;
    private array $unlockedFeatures;

    public function __construct(array $data) {
        $this->level = $data['level'];
        $this->xpRequired = $data['xp_required'];
        $this->coinsReward = $data['coins_reward'];
        $this->premiumCoinsReward = $data['premium_coins_reward'];
        $this->unlockedFeatures = json_decode($data['unlocked_features'] ?? '[]', true);
    }

    public function getRewards(): array {
        return [
            'coins' => $this->coinsReward,
            'premium_coins' => $this->premiumCoinsReward,
            'features' => $this->unlockedFeatures
        ];
    }

    // Getters
    public function getLevel(): int { return $this->level; }
    public function getXpRequired(): int { return $this->xpRequired; }
    public function getCoinsReward(): int { return $this->coinsReward; }
    public function getPremiumCoinsReward(): int { return $this->premiumCoinsReward; }
    public function getUnlockedFeatures(): array { return $this->unlockedFeatures; }
}