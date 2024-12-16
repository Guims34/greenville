<?php
namespace App\Classes\Missions;

use GameTime;
use TimeDisplay;

class Mission {
    private int $id;
    private string $title;
    private string $description;
    private string $type;
    private int $targetValue;
    private int $xpReward;
    private int $coinsReward;
    private int $progress = 0;
    private bool $completed = false;
    private bool $claimed = false;
    private \DateTime $expiresAt;
    private \DateTime $startedAt;
    private int $gameDaysElapsed = 0;

    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->type = $data['type'];
        $this->targetValue = $data['target_value'];
        $this->xpReward = $data['xp_reward'];
        $this->coinsReward = $data['coins_reward'];
        $this->progress = $data['progress'] ?? 0;
        $this->completed = $data['completed'] ?? false;
        $this->claimed = $data['claimed'] ?? false;
        $this->expiresAt = new \DateTime($data['expires_at'] ?? 'tomorrow');
        $this->startedAt = new \DateTime($data['started_at'] ?? 'now');
        $this->gameDaysElapsed = $data['game_days_elapsed'] ?? 0;
    }

    public function updateProgress(int $value): bool {
        if ($this->completed || $this->isExpired()) {
            return false;
        }

        $this->progress = min($this->targetValue, $value);
        $this->completed = $this->progress >= $this->targetValue;
        
        // Mettre à jour les jours de jeu écoulés
        $this->gameDaysElapsed = GameTime::getGameDays(
            $this->startedAt->getTimestamp()
        );

        return $this->completed;
    }

    public function isExpired(): bool {
        // Vérifier si la mission a expiré en temps de jeu
        $gameDaysRemaining = GameTime::getRemainingGameDays(
            $this->expiresAt->getTimestamp()
        );
        return $gameDaysRemaining <= 0;
    }

    public function getTimeRemaining(): string {
        $gameDaysRemaining = GameTime::getRemainingGameDays(
            $this->expiresAt->getTimestamp()
        );
        return TimeDisplay::formatGameDuration($gameDaysRemaining);
    }

    public function getProgressPercentage(): float {
        return min(100, ($this->progress / $this->targetValue) * 100);
    }

    public function getRewards(): array {
        // Bonus basé sur la rapidité de complétion
        $speedBonus = max(0, 24 - $this->gameDaysElapsed) * 0.05; // 5% par jour restant
        
        return [
            'xp' => (int)($this->xpReward * (1 + $speedBonus)),
            'coins' => (int)($this->coinsReward * (1 + $speedBonus))
        ];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getType(): string { return $this->type; }
    public function getTargetValue(): int { return $this->targetValue; }
    public function getProgress(): int { return $this->progress; }
    public function isCompleted(): bool { return $this->completed; }
    public function isClaimed(): bool { return $this->claimed; }
    public function getExpiresAt(): \DateTime { return $this->expiresAt; }
    public function getStartedAt(): \DateTime { return $this->startedAt; }
    public function getGameDaysElapsed(): int { return $this->gameDaysElapsed; }
}
