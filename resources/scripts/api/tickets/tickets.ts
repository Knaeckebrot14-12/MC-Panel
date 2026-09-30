import http from '@/api/http';

export interface TicketSummary {
    id: number;
    subject: string;
    category: string;
    priority: string;
    status: string;
    lastReplyAt: string | null;
    createdAt: string;
    rating: number | null;
}

export interface TicketMessage {
    id: number;
    body: string;
    isStaff: boolean;
    author: string;
    createdAt: string;
}

export interface TicketDetail extends TicketSummary {
    serverName: string | null;
    messages: TicketMessage[];
}

export interface TicketList {
    data: TicketSummary[];
    meta: { currentPage: number; lastPage: number; total: number };
}

export const getTickets = (page = 1): Promise<TicketList> =>
    http.get('/api/client/tickets', { params: { page } }).then(({ data }) => data);

export const getTicket = (id: number): Promise<TicketDetail> =>
    http.get(`/api/client/tickets/${id}`).then(({ data }) => data);

export const getTicketServers = (): Promise<{ id: number; name: string }[]> =>
    http.get('/api/client/tickets/servers').then(({ data }) => data);

export interface NewTicket {
    subject: string;
    category: string;
    priority: string;
    serverId: number | null;
    message: string;
}

export const createTicket = (values: NewTicket): Promise<TicketDetail> =>
    http.post('/api/client/tickets', values).then(({ data }) => data);

export const replyToTicket = (id: number, message: string): Promise<TicketDetail> =>
    http.post(`/api/client/tickets/${id}/reply`, { message }).then(({ data }) => data);

export const closeTicket = (id: number): Promise<TicketDetail> =>
    http.post(`/api/client/tickets/${id}/close`).then(({ data }) => data);

export const rateTicket = (id: number, rating: 'up' | 'down'): Promise<TicketDetail> =>
    http.post(`/api/client/tickets/${id}/rate`, { rating }).then(({ data }) => data);

export const reopenTicket = (id: number): Promise<TicketDetail> =>
    http.post(`/api/client/tickets/${id}/reopen`).then(({ data }) => data);
