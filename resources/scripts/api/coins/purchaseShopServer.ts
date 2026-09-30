import http from '@/api/http';

interface Data {
    name: string;
    nodeId: number;
    eggId: number;
    planId?: number;
}

export default ({ name, nodeId, eggId, planId }: Data): Promise<string> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/coins/shop/server', {
            name,
            node_id: nodeId,
            egg_id: eggId,
            plan_id: planId,
        })
            .then(({ data }) => resolve(data.data.identifier))
            .catch(reject);
    });
};
