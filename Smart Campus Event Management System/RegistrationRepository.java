package com.campus.eventmanagement.repository;

import com.campus.eventmanagement.model.Registration;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

public interface RegistrationRepository extends JpaRepository<Registration, Long> {

    List<Registration> findByEventId(Long id);
}
