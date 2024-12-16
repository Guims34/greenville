export interface User {
  id: string;
  username: string;
  email: string;
  avatar?: string;
  level: number;
  coins: number;
  premiumCoins: number;
  createdAt: Date;
}

export interface AuthState {
  user: User | null;
  isLoading: boolean;
  error: string | null;
}