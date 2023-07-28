export default [
    {
        path: '/operations/planting',
        name: 'operations-planting',
        component: () => import('@/views/operations/Planting.vue'),
        meta: { auth: 'ROLE_OPERATOR' }
    }
];
