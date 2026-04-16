package com.campus.eventmanagement.service;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import com.campus.eventmanagement.model.Event;
import com.campus.eventmanagement.repository.EventRepository;

import java.util.List;

@Service
public class EventService {

    @Autowired
    private EventRepository repo;

    public List<Event> getAllEvents() {
        return repo.findAll();
    }

    public void save(Event event) {
        repo.save(event);
    }

    public void delete(Long id) {
        repo.deleteById(id);
    }

    public Event getById(Long id) {
        return repo.findById(id).orElse(null);
    }
}
