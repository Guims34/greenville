import { io } from 'socket.io-client';
import { useAuthStore } from '../store/authStore';
import { useChatStore } from '../store/chatStore';
import toast from 'react-hot-toast';

const SOCKET_URL = import.meta.env.VITE_API_URL || 'http://localhost:4000';

class SocketService {
  private static instance: SocketService;
  private socket: any;
  private reconnectAttempts: number = 0;
  private maxReconnectAttempts: number = 5;

  private constructor() {
    this.initializeSocket();
  }

  public static getInstance(): SocketService {
    if (!SocketService.instance) {
      SocketService.instance = new SocketService();
    }
    return SocketService.instance;
  }

  private initializeSocket() {
    const auth = useAuthStore.getState();
    const chatStore = useChatStore.getState();

    this.socket = io(SOCKET_URL, {
      auth: {
        token: auth.token
      },
      reconnection: true,
      reconnectionAttempts: this.maxReconnectAttempts,
      reconnectionDelay: 1000,
      timeout: 10000
    });

    this.setupEventListeners(chatStore);
  }

  private setupEventListeners(chatStore: any) {
    this.socket.on('connect', () => {
      console.log('Connected to chat server');
      this.reconnectAttempts = 0;
    });

    this.socket.on('disconnect', (reason: string) => {
      console.log('Disconnected from chat server:', reason);
      if (reason === 'io server disconnect') {
        this.socket.connect();
      }
    });

    this.socket.on('connect_error', (error: Error) => {
      console.error('Connection error:', error);
      this.reconnectAttempts++;
      
      if (this.reconnectAttempts >= this.maxReconnectAttempts) {
        toast.error('Impossible de se connecter au serveur de chat');
      }
    });

    this.socket.on('message', (message: any) => {
      chatStore.addMessage(message);
    });

    this.socket.on('reaction', ({ messageId, userId, emoji, type }: any) => {
      if (type === 'add') {
        chatStore.addReaction(messageId, userId, emoji);
      } else {
        chatStore.removeReaction(messageId, userId, emoji);
      }
    });

    this.socket.on('users', (users: string[]) => {
      chatStore.setOnlineUsers(users);
    });
  }

  public sendMessage(message: any) {
    if (!this.socket?.connected) {
      toast.error('Non connecté au serveur de chat');
      return;
    }
    this.socket.emit('message', message);
  }

  public addReaction(messageId: string, emoji: string) {
    if (!this.socket?.connected) return;
    this.socket.emit('reaction', { messageId, emoji, type: 'add' });
  }

  public removeReaction(messageId: string, emoji: string) {
    if (!this.socket?.connected) return;
    this.socket.emit('reaction', { messageId, emoji, type: 'remove' });
  }

  public disconnect() {
    if (this.socket) {
      this.socket.disconnect();
    }
  }
}

export const socketService = SocketService.getInstance();