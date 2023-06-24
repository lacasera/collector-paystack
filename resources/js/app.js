window.axios = require('axios')
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import React from "react"
import { createInertiaApp } from '@inertiajs/react'
import {render} from "react-dom";
createInertiaApp({
    resolve: name => require(`./Pages/${name}`),
    setup({ el, App, props }) {
        render(<App {...props} />, el)
    },
})
