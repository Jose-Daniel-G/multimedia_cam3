import { TestBed } from '@angular/core/testing';

import { AdminLteService } from './admin-lte.service';

describe('AdminLteService', () => {
  let service: AdminLteService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AdminLteService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
