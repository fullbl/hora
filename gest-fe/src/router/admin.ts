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
        path: '/orders',
        name: 'orders',
        component: () => import('@/views/pages/Orders.vue'),
        meta: { auth: 'ROLE_ADMIN' }
    },
    {
        path: '/deliveries',
        name: 'deliveries',
        component: () => import('@/views/pages/Deliveries.vue'),
        meta: { auth: 'ROLE_ADMIN' }
    }
];
