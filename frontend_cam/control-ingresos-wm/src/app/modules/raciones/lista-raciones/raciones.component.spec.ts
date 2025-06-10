import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RacionesComponent } from './raciones.component';

describe('RacionesComponent', () => {
  let component: RacionesComponent;
  let fixture: ComponentFixture<RacionesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RacionesComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RacionesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
