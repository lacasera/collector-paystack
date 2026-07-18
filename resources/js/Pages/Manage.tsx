import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import Layout from '../Layouts/Layout';
import SectionNav, { SectionDefinition } from '../Components/Manage/SectionNav';
import SubscriptionSummary from '../Components/Manage/SubscriptionSummary';
import CurrentPlan, { CurrentPlanData } from '../Components/Manage/CurrentPlan';
import PaymentHistory, { PaginationMeta, TransactionRow } from '../Components/Manage/PaymentHistory';
import PaymentMethods, { PaymentMethodRow } from '../Components/Manage/PaymentMethods';
import SubscriptionHistory, { SubscriptionRow } from '../Components/Manage/SubscriptionHistory';
import CancelSubscription from '../Components/Modal/CancelSubscription';

interface ManageProps {
    section: string;
    collectable: { email: string };
    currentPlan: CurrentPlanData | null;
    paymentMethods: PaymentMethodRow[];
    transactions: { data: TransactionRow[]; meta: PaginationMeta };
    subscriptions: SubscriptionRow[];
    cancelation: any;
}

const SECTION_TITLES: Record<string, string> = {
    overview: 'Overview',
    history: 'Payment history',
    methods: 'Payment methods',
    subscriptions: 'Subscriptions',
};

/**
 * Build the portal URL for a section — the canonical link an application can
 * redirect to, and what the address bar shows while browsing.
 */
export function sectionUrl(manageUrl: string, section: string, page?: number): string {
    const query = new URLSearchParams();

    if (section !== 'overview') {
        query.set('section', section);
    }

    if (page && page > 1) {
        query.set('page', String(page));
    }

    return query.toString() ? `${manageUrl}?${query}` : manageUrl;
}

export default function Manage(props: ManageProps): React.JSX.Element {
    const { collector } = usePage().props as any;
    const [section, setSection] = React.useState<string>(props.section);
    const [showCancel, setShowCancel] = React.useState<boolean>(false);
    const [updatingCard, setUpdatingCard] = React.useState<boolean>(false);

    /**
     * Switching section only changes the URL, never refetches: the page already
     * holds every section's data, and a server round-trip would re-run the
     * PayStack calls for nothing.
     */
    const selectSection = (key: string) => {
        setSection(key);
        window.history.pushState({ section: key }, '', sectionUrl(collector.urls.manage, key));
    };

    // Keep the browser's back and forward buttons meaningful across sections.
    React.useEffect(() => {
        const syncFromUrl = () => {
            const params = new URLSearchParams(window.location.search ?? '');

            setSection(params.get('section') ?? 'overview');
        };

        window.addEventListener('popstate', syncFromUrl);

        return () => window.removeEventListener('popstate', syncFromUrl);
    }, []);

    const sections: SectionDefinition[] = [
        { key: 'overview', label: 'Overview' },
        { key: 'history', label: 'Payment history', count: props.transactions.meta.total },
        { key: 'methods', label: 'Payment methods', count: props.paymentMethods.length },
        { key: 'subscriptions', label: 'Subscriptions', count: props.subscriptions.length },
    ];

    /**
     * PayStack has no card-replacement API, so this mints a short-lived link to
     * their hosted page and sends the customer there.
     */
    const updatePaymentMethod = async () => {
        setUpdatingCard(true);

        try {
            const { data } = await window.axios.post<{ redirect?: string }>(collector.urls.updatePaymentMethod);

            if (data?.redirect) {
                window.location.href = data.redirect;

                return;
            }
        } catch (error) {
            console.error('Payment method update error:', error);
        }

        setUpdatingCard(false);
    };

    return (
        <Layout>
            <Head title='Manage Subscription' />

            <CancelSubscription
                show={showCancel}
                details={props.cancelation}
                onCloseModal={() => setShowCancel(false)}
            />

            <div className='max-w-5xl mx-auto px-5 w-full'>
                <header className='flex flex-row items-baseline justify-between flex-wrap gap-2 mb-6'>
                    <h1 className='font-bold text-2xl text-gray-900 leading-tight'>Billing</h1>
                    <p className='text-[15px] text-gray-600'>{props.collectable?.email}</p>
                </header>

                <div className='mb-8'>
                    <SubscriptionSummary
                        plan={props.currentPlan}
                        // The subscription's own card where PayStack gives one;
                        // a stored card is only a fallback for display.
                        card={props.currentPlan?.card ?? props.paymentMethods[0] ?? null}
                        changePlanUrl={collector.urls.changePlan}
                        onCancel={() => setShowCancel(true)}
                    />
                </div>

                <div className='flex flex-col sm:flex-row gap-6 pb-12'>
                    <aside className='sm:w-[190px] sm:shrink-0'>
                        <SectionNav sections={sections} active={section} onChange={selectSection} />
                    </aside>

                    <section className='flex-1 min-w-0'>
                        <h2 className='font-semibold text-gray-900 mb-3'>{SECTION_TITLES[section]}</h2>

                        {section === 'overview' && <CurrentPlan plan={props.currentPlan} />}

                        {section === 'history' && (
                            <PaymentHistory
                                transactions={props.transactions.data}
                                meta={props.transactions.meta}
                                // Paging is a real visit (the next page comes
                                // from PayStack), so the URL has to carry the
                                // section or it would land back on Overview.
                                pageUrl={page => sectionUrl(collector.urls.manage, 'history', page)}
                            />
                        )}

                        {section === 'methods' && (
                            <PaymentMethods
                                methods={props.paymentMethods}
                                // A cancelled subscription cannot have its card
                                // changed, matching what the endpoint allows.
                                canUpdate={Boolean(props.currentPlan && ! props.currentPlan.onGracePeriod)}
                                updating={updatingCard}
                                onUpdate={updatePaymentMethod}
                            />
                        )}

                        {section === 'subscriptions' && <SubscriptionHistory subscriptions={props.subscriptions} />}
                    </section>
                </div>
            </div>
        </Layout>
    );
}
