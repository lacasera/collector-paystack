import {ToastProvider} from "react-toast-notifications";

window.axios = require('axios')
//window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';
window.axios.defaults.headers.common['Accept'] = 'application/json';

import React from "react"
import { createInertiaApp } from '@inertiajs/react'
import {render} from "react-dom";
createInertiaApp({
    resolve: name => require(`./Pages/${name}`),
    setup({ el, App, props }) {
        render(<ToastProvider><App {...props} /></ToastProvider>, el)
    },
})
