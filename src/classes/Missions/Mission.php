<?php
namespace App\Classes\Missions;

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
    }

    public function updateProgress(int $value): bool {
        if ($this->completed || $this->isExpired()) {
            return false;
        }

        $this->progress = min($this->targetValue, $value);
        $this->completed = $this->progress >= $this->targetValue;
        return $this->completed;
    }

    public function isExpired(): bool {
        return $this->expiresAt <= new \DateTime();
    }

    public function getRewards(): array {
        return [
            'xp' => $this->xpReward,
            'coins' => $this->coinsReward
        ];
    }

    public function getProgressPercentage(): float {
        return min(100, ($this->progress / $this->targetValue) * 100);
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
}