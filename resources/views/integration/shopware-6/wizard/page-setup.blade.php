<div class="wizard-page wizard-page--setup">
    @markdown(trans('shopware-6/wizard.pages.setup.connect'))

    <a class="sw-button sw-button--primary" href="#">
        <span class="sw-button__content">
            {{ trans('shopware-6/wizard.navigation.route.google-data-studio') }}
        </span>
    </a>

    @markdown(trans('shopware-6/wizard.pages.setup.content'))

    <div class="sw-field sw-block-field sw-field--text sw-field--default">
        <div class="sw-block-field__block">
            <input type="text" placeholder="" value="{{ $shop->id }}" readonly>
        </div>
    </div>

    @include('integration.shopware-6.wizard.navigation', [
        'previous' => 'intro',
        'next' => 'finish',
    ])
</div>
