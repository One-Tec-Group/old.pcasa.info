import {BrowserModule} from '@angular/platform-browser';
import {NgModule} from '@angular/core';
import {CoreModule} from 'common/core/core.module';
import {AppComponent} from './app.component';
import {AppRoutingModule} from './app-routing.module';
import {AuthModule} from 'common/auth/auth.module';
import {AccountSettingsModule} from 'common/account-settings/account-settings.module';
import {SharedModule} from './shared/shared.module';
import {DashboardModule} from './dashboard/dashboard.module';
import {BrowserAnimationsModule} from "@angular/platform-browser/animations";
import {RouterModule} from '@angular/router';
import {PagesModule} from '../common/core/pages/pages.module';
import {APP_CONFIG} from '../common/core/config/vebto-config';
import {ARCHITECT_CONFIG} from './architect-config';

@NgModule({
    declarations: [
        AppComponent
    ],
    imports: [
        BrowserModule,
        BrowserAnimationsModule,
        RouterModule,
        CoreModule.forRoot(),
        AuthModule,
        AccountSettingsModule,
        AppRoutingModule,
        DashboardModule,
        SharedModule,
        PagesModule,
    ],
    providers: [
        {
            provide: APP_CONFIG,
            useValue: ARCHITECT_CONFIG,
            multi: true,
        },
    ],
    bootstrap: [AppComponent]
})
export class AppModule {
}
