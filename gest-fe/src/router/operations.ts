export default [
    {
        path: '/operations/soaking',
        name: 'operations-soaking',
        component: () => import('@/views/operations/Soaking.vue'),
        meta: { auth: 'ROLE_OPERATOR' }
    }
];
