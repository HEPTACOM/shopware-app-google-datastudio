<div class="sw-page__content">
    <div class="sw-page__main-content">
        <div class="sw-page__main-content-inner">
            <div class="sw-card-view">
                <div class="sw-card-view__content">
                    <div class="sw-card">
                        <div class="sw-card__title">
                            {{ trans('shopware-6/wizard.header.title') }}
                        </div>
                        <div class="sw-card__subtitle">
                            {{ trans('shopware-6/wizard.header.subtitle') }}
                        </div>
                        <div class="sw-card__content wizard wizard-page-selected--intro">
                            @include('integration.shopware-6.wizard.page-intro')
                            @include('integration.shopware-6.wizard.page-data-config')
                            @include('integration.shopware-6.wizard.page-setup')
                            @include('integration.shopware-6.wizard.page-finish')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (file_exists(public_path('storage/shopware-admin/v'.$swVersion.'/static/css/app.css')))
    <link rel="stylesheet" href="/storage/shopware-admin/v{{ $swVersion }}/static/css/app.css">
@elseif (file_exists(public_path('storage/shopware-admin/v6.4.0.0/static/css/app.css')))
    <link rel="stylesheet" href="/storage/shopware-admin/v6.4.0.0/static/css/app.css">
@else
    <link rel="stylesheet" href="{{ request('shop-url') }}/bundles/administration/static/css/app.css?{{ request('timestamp') }}">
@endif

<style>
    .wizard-page {
        display: none;
    }

    .wizard-page p {
        margin-bottom: 16px;
        white-space: pre-wrap;
    }

    footer {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }

    .wizard-page.wizard-page--data-config ul {
        padding-left: 20px;
        margin: 20px 0;
    }

    .sw-card__content.wizard-page-selected--intro .wizard-page.wizard-page--intro {
        display: block;
    }

    .sw-card__content.wizard-page-selected--data-config .wizard-page.wizard-page--data-config {
        display: block;
    }

    .sw-card__content.wizard-page-selected--setup .wizard-page.wizard-page--setup {
        display: block;
    }

    .sw-card__content.wizard-page-selected--finish .wizard-page.wizard-page--finish {
        display: block;
    }
</style>

<script type="application/javascript">
    function wizardGoTo(pageId) {
        /** @var wizard {HTMLDivElement} */
        for (let wizard of document.querySelectorAll('.wizard')) {
            let classesToRemote = [];

            for (let className of wizard.classList.values()) {
                if (className.match(/^wizard-page-selected--/)) {
                    classesToRemote.push(className);
                }
            }

            for (let className of classesToRemote) {
                wizard.classList.remove(className);
            }

            wizard.classList.add('wizard-page-selected--' + pageId);
        }
    }

    function sendReadyState() {
        window.parent.postMessage('sw-app-loaded', '*');
    }

    sendReadyState();
</script>
