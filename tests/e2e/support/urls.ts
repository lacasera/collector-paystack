/**
 * The billing portal path the specs assert against.
 *
 * The portal URI is configurable (`collector.prefix` + `collector.path`), so
 * the specs derive it rather than hard-coding it. Run the suite against a
 * relocated portal with, for example:
 *
 *   COLLECTOR_PREFIX=account COLLECTOR_PORTAL_PATH=/account/billing npm run test:e2e
 */
export const PORTAL_PATH = process.env.COLLECTOR_PORTAL_PATH ?? '/collector/billing';

/** Escaped for use inside a RegExp literal. */
const escaped = PORTAL_PATH.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

/** Matches the portal path anywhere in the URL (e.g. with a query string). */
export const PORTAL_URL = new RegExp(escaped);

/** Matches the portal path at the end of the URL — i.e. no query string. */
export const CLEAN_PORTAL_URL = new RegExp(`${escaped}$`);

/** The management portal, which hangs off the portal path. */
export const MANAGE_PATH = `${PORTAL_PATH}/manage`;

export const MANAGE_URL = new RegExp(`${escaped}/manage$`);

/**
 * The plans page reached from the management portal. Subscribers are forwarded
 * to the management portal unless this flag is present.
 */
export const CHANGE_PLAN_PATH = `${PORTAL_PATH}?change=1`;
