export interface PushService {
    supported: boolean;
    public_key: string | null;
    devices: number;
}
