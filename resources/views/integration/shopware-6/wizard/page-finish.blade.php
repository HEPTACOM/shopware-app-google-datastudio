<div class="wizard-page wizard-page--finish">
    @markdown(trans('shopware-6/wizard.pages.finish.content'))

    <div style="text-align: center; margin: 30px 0">
        <a class="sw-button sw-button--primary" href="#">
            <span class="sw-button__content">
                {{ trans('shopware-6/wizard.navigation.route.youtube') }}
            </span>
        </a>
    </div>

    @include('integration.shopware-6.wizard.navigation', [
        'previous' => 'setup',
    ])
</div>
