// export button
import './module/sw-order/page/sw-order-detail';
import './component/export-component-button';
import './app';

// flow
import './extension/sw-flow-sequence-action';
import './module';


// logs tab
import './view/sw-tiger-connect-logging-tab';
Shopware.Module.register('sw-tiger-connect-logging-tab', {
    routeMiddleware(next, currentRoute) {
        const customRouteName = 'sw.tiger-connect.order.detail.logs';

        if (
            currentRoute.name === 'sw.order.detail'
            && currentRoute.children.every((currentRoute) => currentRoute.name !== customRouteName)
        ) {
            currentRoute.children.push({
                name: customRouteName,
                path: 'logs',
                component: 'sw-tiger-connect-order-detail-logs',
                meta: {
                    parentPath: 'sw.order.index'
                }
            });
        }
        next(currentRoute);
    }
});

Shopware.Component.register('mail-select-config', () => import('./component/mail-select-config'));
Shopware.Component.register('line-item-type-selection', () => import('./component/line-item-type-selection'));