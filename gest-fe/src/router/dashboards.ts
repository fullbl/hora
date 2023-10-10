export default [
   
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