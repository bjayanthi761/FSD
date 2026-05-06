package com.campus.eventmanagement.service;

import com.campus.eventmanagement.model.Registration;
import com.campus.eventmanagement.repository.RegistrationRepository;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
public class RegistrationService {

    @Autowired
    private RegistrationRepository registrationRepository;

    public void save(Registration registration) {
        registrationRepository.save(registration);
    }

    public List<Registration> getByEventId(Long id) {
        return registrationRepository.findByEventId(id);
    }
}
