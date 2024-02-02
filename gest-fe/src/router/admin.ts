export default [
    {
        path: '/users',
        name: 'users',
        component: () => import('@/views/pages/Users.vue'),
        meta: { auth: 'ROLE_ADMIN' }
    },
    {
        path: '/products',
        name: 'products',
        component: () => import('@/views/pages/Products.vue'),
        meta: { auth: 'ROLE_ADMIN' }
    },
    {
        path: '/zones',
        name: 'zones',
        component: () => import('@/views/pages/Zones.vue'),
        meta: { auth: 'ROLE_SUPER_ADMIN' }
    },
    {
        path: '/deliveries',
        name: 'deliveries',
        component: () => import('@/views/pages/Deliveries.vue'),
        meta: { auth: 'ROLE_ADMIN' }
    }
];
