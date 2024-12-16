<?php
namespace App\Classes\Progression;

class Achievement {
    private int $id;
    private string $title;
    private string $description;
    private string $category;
    private string $difficulty;
    private int $targetValue;
    private int $xpReward;
    private int $coinsReward;
    private int $premiumCoinsReward;
    private string $icon;
    private bool $completed = false;
    private int $progress = 0;

    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->category = $data['category'];
        $this->difficulty = $data['difficulty'];
        $this->targetValue = $data['target_value'];
        $this->xpReward = $data['xp_reward'];
        $this->coinsReward = $data['coins_reward'];
        $this->premiumCoinsReward = $data['premium_coins_reward'];
        $this->icon = $data['icon'];
        $this->completed = $data['completed'] ?? false;
        $this->progress = $data['progress'] ?? 0;
    }

    public function updateProgress(int $value): bool {
        if ($this->completed) {
            return false;
        }

        $this->progress = min($this->targetValue, $value);
        $this->completed = $this->progress >= $this->targetValue;
        return $this->completed;
    }

    public function getProgressPercentage(): float {
        return min(100, ($this->progress / $this->targetValue) * 100);
    }

    public function getRewards(): array {
        return [
            'xp' => $this->xpReward,
            'coins' => $this->coinsReward,
            'premium_coins' => $this->premiumCoinsReward
        ];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getCategory(): string { return $this->category; }
    public function getDifficulty(): string { return $this->difficulty; }
    public function getTargetValue(): int { return $this->targetValue; }
    public function getProgress(): int { return $this->progress; }
    public function isCompleted(): bool { return $this->completed; }
    public function getIcon(): string { return $this->icon; }
}