import {NgModule} from '@angular/core';
import {Templates} from './templates/templates.service';
import {Themes} from './themes/themes.service';
import {ProjectUrl} from './projects/project-url.service';
import {PublishProjectModalComponent} from './projects/publish-project-modal/publish-project-modal.component';
import {MaterialModule} from '../material.module';
import {CommonModule} from '@angular/common';
import {UiModule} from 'common/core/ui/ui.module';
import {FormsModule} from '@angular/forms';
import {DomHelpers} from './dom-helpers.service';
import {Projects} from "./projects/projects.service";
import {CrupdateProjectModalComponent} from './crupdate-project-modal/crupdate-project-modal.component';

@NgModule({
    imports: [
        CommonModule,
        UiModule,
        FormsModule,
        MaterialModule,
    ],
    declarations: [
        PublishProjectModalComponent,
        CrupdateProjectModalComponent,
    ],
    entryComponents: [
        PublishProjectModalComponent,
        CrupdateProjectModalComponent,
    ],
    providers: [
        Templates,
        Themes,
        ProjectUrl,
        DomHelpers,
        Projects,
    ],
})
export class SharedModule {
}
