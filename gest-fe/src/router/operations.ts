export default [
    {
        path: '/operations/soaking',
        name: 'operations-soaking',
        component: () => import('@/views/operations/Soaking.vue'),
        meta: { auth: 'ROLE_OPERATOR' }
    },
    {
        path: '/operations/planting',
        name: 'operations-planting',
        component: () => import('@/views/operations/Planting.vue'),
        meta: { auth: 'ROLE_OPERATOR' }
    }
];
