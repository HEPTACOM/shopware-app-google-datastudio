<div class="wizard-page wizard-page--setup">
    @markdown(trans('shopware-6/wizard.pages.setup.connect'))

    <div style="text-align: center; margin: 30px 0">
        <a class="sw-button sw-button--primary"
           href="{{ config('heptaconnect-shopware-six.google_apps_script_deployment_url') }}"
           target="_blank"
           rel="noopener"
        >
            <span class="sw-button__content">
                {{ trans('shopware-6/wizard.navigation.route.google-data-studio') }}
            </span>
        </a>
    </div>

    @markdown(trans('shopware-6/wizard.pages.setup.content'))

    <div class="sw-field sw-block-field sw-field--text sw-field--default">
        <div class="sw-block-field__block" style="margin: 0 auto; max-width: 300px">
            <input type="text" placeholder="" value="{{ $shop->id }}" readonly style="text-align: center">
        </div>
    </div>

    @include('integration.shopware-6.wizard.navigation', [
        'previous' => 'intro',
        'next' => 'finish',
    ])
</div>
