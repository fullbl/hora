import { createRouter, createWebHashHistory } from 'vue-router';
import authService from '@/service/AuthService';
import admin from './admin';
import dashboards from './dashboards';
import AppLayout from '@/layout/AppLayout.vue';
import operations from './operations';
declare module 'vue-router' {
    interface RouteMeta {
        auth?: 'ROLE_CUSTOMER' | 'ROLE_ADMIN' | 'ROLE_OPERATOR';
    }
}

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
            children: admin.concat(operations, dashboards)
        }
    ]
});

router.beforeEach(async (to) => {
    if ('undefined' !== typeof to.meta.auth && !authService.isGranted(to.meta.auth)) {
        return { name: 'login' };
    }
});

export default router;
