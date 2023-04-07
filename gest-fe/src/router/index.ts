import { createRouter, createWebHashHistory } from 'vue-router';
import authService from '@/service/AuthService';

declare module 'vue-router' {
    interface RouteMeta {
        auth?: 'ROLE_CUSTOMER' | 'ROLE_ADMIN' | 'ROLE_OPERATOR'
    }
}

import AppLayout from '@/layout/AppLayout.vue';

const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        {
            path: '/',
            component: AppLayout,
            children: [
                {
                    path: '/users',
                    name: 'users',
                    component: () => import('@/views/Users.vue'),
                    meta: { auth: 'ROLE_ADMIN' }
                },
                {
                    path: '/products',
                    name: 'products',
                    component: () => import('@/views/Products.vue'),
                    meta: { auth: 'ROLE_ADMIN' }
                },
                {
                    path: '/orders',
                    name: 'orders',
                    component: () => import('@/views/Orders.vue'),
                    meta: { auth: 'ROLE_ADMIN' }
                },
                {
                    path: '/deliveries',
                    name: 'deliveries',
                    component: () => import('@/views/Deliveries.vue'),
                    meta: { auth: 'ROLE_ADMIN' }
                },

                {
                    path: '/dashboards/storage',
                    name: 'storage-dashboard',
                    component: () => import('@/views/StorageDashboard.vue'),
                    meta: { auth: 'ROLE_OPERATOR' }
                },
                {
                    path: '/dashboards/delivery',
                    name: 'delivery-dashboard',
                    component: () => import('@/views/DeliveryDashboard.vue'),
                    meta: { auth: 'ROLE_OPERATOR' }
                },
                {
                    path: '/dashboards/daily',
                    name: 'daily-dashboard',
                    component: () => import('@/views/DailyDashboard.vue'),
                    meta: { auth: 'ROLE_OPERATOR' }
                },

            ]
        },
        {
            path: '/auth/login',
            name: 'login',
            component: () => import('@/views/pages/auth/Login.vue')
        }
    ]
});

router.beforeEach(async to => {
    if ('undefined' !== typeof to.meta.auth && !authService.isGranted(to.meta.auth)) {
        return { name: 'login' }
    }
})

export default router;
