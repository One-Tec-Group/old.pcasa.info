import {
    MatButtonModule, MatCheckboxModule, MatDialogModule,
    MatTooltipModule, MatMenuModule, MatListModule,
    MatProgressBarModule, MatSlideToggleModule,
} from '@angular/material';
import {NgModule} from "@angular/core";

@NgModule({
    imports: [
        MatButtonModule,
        MatCheckboxModule,
        MatTooltipModule,
        MatDialogModule,
        MatMenuModule,
        MatListModule,
        MatSlideToggleModule,
        MatProgressBarModule,
    ],
    exports: [
        MatButtonModule,
        MatCheckboxModule,
        MatTooltipModule,
        MatDialogModule,
        MatMenuModule,
        MatListModule,
        MatProgressBarModule,
        MatSlideToggleModule,
    ],
})
export class MaterialModule { }