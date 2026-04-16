package com.campus.eventmanagement.service;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import com.campus.eventmanagement.model.Registration;
import com.campus.eventmanagement.repository.RegistrationRepository;

import java.util.List;

@Service
public class RegistrationService {

    @Autowired
    private RegistrationRepository repo;

    public void save(Registration r) {
        repo.save(r);
    }

    public List<Registration> getAll() {
        return repo.findAll();
    }
}
