import { createRouter, createWebHashHistory } from 'vue-router';
import authService from '@/service/AuthService';

declare module 'vue-router' {
    interface RouteMeta {
        auth?: 'ROLE_CUSTOMER' | 'ROLE_ADMIN' | 'ROLE_OPERATOR';
    }
}

import AppLayout from '@/layout/AppLayout.vue';

const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        {
            path: '/',
            name: 'login',
            component: () => import('@/views/pages/auth/Login.vue')
        },
        {
            path: '/',
            component: AppLayout,
            children: [
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
                },

                {
                    path: '/dashboards/storage',
                    name: 'storage-dashboard',
                    component: () => import('@/views/dashboards/StorageDashboard.vue'),
                    meta: { auth: 'ROLE_OPERATOR' }
                },
                {
                    path: '/dashboards/steps',
                    name: 'steps-dashboard',
                    component: () => import('@/views/dashboards/StepsDashboard.vue'),
                    meta: { auth: 'ROLE_OPERATOR' }
                },
                {
                    path: '/dashboards/planting',
                    name: 'planting-dashboard',
                    component: () => import('@/views/dashboards/PlantingDashboard.vue'),
                    meta: { auth: 'ROLE_OPERATOR' }
                },
                {
                    path: '/dashboards/harvest',
                    name: 'harvest-dashboard',
                    component: () => import('@/views/dashboards/HarvestDashboard.vue'),
                    meta: { auth: 'ROLE_OPERATOR' }
                },
                {
                    path: '/dashboards/delivery',
                    name: 'delivery-dashboard',
                    component: () => import('@/views/dashboards/DeliveryDashboard.vue'),
                    meta: { auth: 'ROLE_OPERATOR' }
                },
                {
                    path: '/dashboards/payment',
                    name: 'payment-dashboard',
                    component: () => import('@/views/dashboards/PaymentDashboard.vue'),
                    meta: { auth: 'ROLE_OPERATOR' }
                },
            ]
        }
    ]
});

router.beforeEach(async (to) => {
    if ('undefined' !== typeof to.meta.auth && !authService.isGranted(to.meta.auth)) {
        return { name: 'login' };
    }
});

export default router;
