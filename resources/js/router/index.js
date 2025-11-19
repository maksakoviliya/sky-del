import { createRouter, createWebHistory } from "vue-router";
import store from "../store";
import guest from "./middleware/guest";
import auth from "./middleware/auth";
import admin from "./middleware/admin";
import notAdmin from "./middleware/notAdmin";

function middlewarePipeline(context, middleware, index) {
  const nextMiddleware = middleware[index]

  if (!nextMiddleware) {
    return context.next
  }

  return () => {
    const nextPipeline = middlewarePipeline(
        context, middleware, index + 1
    )

    nextMiddleware({...context, next: nextPipeline})

  }
}

const routes = [
  {
    path: "/",
    redirect: { name: 'home' }
  },
  {
    path: "/orders/:id?/:view?",
    name: "home",
    meta: { middleware: [auth, notAdmin] },
    component: () => import(/* webpackChunkName: "home" */ "../views/Home"),
  },
  {
    path: "/acts/:id?",
    name: "acts",
    meta: { middleware: [auth, notAdmin] },
    component: () => import(/* webpackChunkName: "home" */ "../views/Acts"),
  },
  {
    path: "/profile",
    name: "profile",
    meta: { middleware: [auth] },
    component: () => import(/* webpackChunkName: "home" */ "../views/Profile"),
  },
  {
    path: "/dashboard",
    name: "dashboard",
    meta: { middleware: [auth, admin] },
    component: () => import(/* webpackChunkName: "home" */ "../views/admin/Dashboard"),
    children: [
      // {
      //   path: "",
      //   name: "analytics",
      //   meta: { middleware: [auth, admin] },
      //   component: () => import(/* webpackChunkName: "home" */ "../views/admin/Analytics"),
      // },
      {
        path: "",
        redirect: { name: 'clients' }
      },
      {
        path: "/dashboard/clients/:id?",
        name: "clients",
        meta: { middleware: [auth, admin] },
        component: () => import(/* webpackChunkName: "home" */ "../views/admin/clients/ClientsList"),
      },
      {
        path: "/dashboard/clients/:id/export",
        name: "export",
        meta: { middleware: [auth, admin] },
        component: () => import(/* webpackChunkName: "home" */ "../views/admin/clients/ExportOrders"),
      },
      {
        path: "/dashboard/orders/:id?",
        name: "orders",
        meta: { middleware: [auth, admin] },
        component: () => import(/* webpackChunkName: "home" */ "../views/admin/orders/OrdersList"),
      },
      {
        path: "/dashboard/couriers/:id?",
        name: "couriers",
        meta: { middleware: [auth, admin] },
        component: () => import(/* webpackChunkName: "home" */ "../views/admin/couriers/CouriersList"),
      },
      {
        path: "/dashboard/acts",
        name: "allActs",
        meta: { middleware: [auth, admin] },
        component: () => import(/* webpackChunkName: "home" */ "../views/admin/acts/ActsList"),
      }
    ]
  },
  {
    path: "/login",
    name: "login",
    meta: { middleware: [guest] },
    component: () => import(/* webpackChunkName: "login" */ "../views/auth/Login"),
  },
  {
    path: "/register",
    name: "register",
    meta: { middleware: [guest] },
    component: () =>
        import(/* webpackChunkName: "register" */ "../views/auth/Register"),
  },
  {
    path: "/:catchAll(.*)*",
    name: "notFound",
    component: () =>
        import(/* webpackChunkName: "not-found" */ "../views/NotFound"),
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

router.beforeEach((to, from, next) => {
  if (!to.meta.middleware || !to.meta.middleware.length) {
    return next()
  }
  const middleware = to.meta.middleware

  const context = {
    to,
    from,
    next,
    store
  }


  return middleware[0]({
    ...context,
    next: middlewarePipeline(context, middleware, 1)
  })

})

export default router;
